<?php

namespace TTU\Charon\Services\Flows;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\PlagiarismCommunicationService;
use Zeizig\Moodle\Globals\User;

class TeacherModifiesSubmission
{
    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var User */
    private $user;

    /** @var PlagiarismCommunicationService */
    private $plagiarismCommunicationService;

    /**
     * @param CharonGradingService $charonGradingService
     * @param SubmissionsRepository $submissionsRepository
     * @param User $user
     * @param PlagiarismCommunicationService $plagiarismCommunicationService
     */
    public function __construct(
        CharonGradingService $charonGradingService,
        SubmissionsRepository $submissionsRepository,
        User $user,
        PlagiarismCommunicationService $plagiarismCommunicationService
    ) {
        $this->charonGradingService = $charonGradingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->user = $user;
        $this->plagiarismCommunicationService = $plagiarismCommunicationService;
    }

    /**
     * When a teacher assigns a new result for a submission.
     *
     * Most common case is during defences and for defense grade,
     * but can also modify any grade result attached to the submission.
     *
     * Also saves the Confirmed submissions hash in plagiarism if needs are met.
     *
     * @param Submission $submission
     * @param Charon $charon
     * @param $newResults
     *
     * @return Submission
     */
    public function run(Submission $submission, Charon $charon, $newResults)
    {
        global $CFG;
        require_once ($CFG->dirroot . '/mod/charon/lib.php');

        $teacherId = $this->user->currentUserId();

        $this->updateResults($submission, $newResults);

        foreach ($submission->users as $student) {
            $this->charonGradingService->updateGrade($submission, $student->id);

            $this->unconfirmOldSubmissions($submission->charon_id, $submission->id, $student->id);

            $this->submissionsRepository->confirmSubmission($submission, $teacherId);

            $this->charonGradingService->updateProgressByStudentId($submission->charon_id, $submission->id, $student->id, $teacherId, 'Done');

            try {
                update_charon_completion_state($submission, $student->id);
            } catch (\Exception $exception) {
                Log::error('Failed to update completion state. Likely culprit: course module. Error: ' . $exception->getMessage());
            }
        }

        if ($charon->plagiarism_assignment_id and $submission->confirmed == 1 and $charon->allow_submission != 1) {
            $sub_domains = preg_split('/[\/|\\\\]/m', $submission->gitCallback->repo);
            $course_regex = '/.*\.git$/';
            $repoName = '';
            foreach ($sub_domains as $meta) {
                if (preg_match($course_regex, $meta)) {
                    $repoName = explode('.', $meta)[0];
                }
            }
            $dataToPlagiarism = [
                'student_uniid' => strtok($submission->user->username, "@"),
                'course_identifier' => $charon->course,
                'assignment_identifier' => $charon->id,
                'commit_sha' => $submission->git_hash,
                'repository_name' => $repoName
                ];
            try {
                $this->plagiarismCommunicationService->saveDefenseCommit($dataToPlagiarism);
            } catch (GuzzleException $e) {
                Log::error('Failed to save defense in plagiarism. Error: ' . $e->getMessage());
            }
        }

        return $submission;
    }

    /**
     * Replace existing results with new ones.
     *
     * @param Submission $submission
     * @param $newResults
     */
    private function updateResults(Submission $submission, $newResults)
    {
        foreach ($newResults as $result) {
            $existingResult = $submission->results->first(function ($resultLoop) use ($result) {
                return $resultLoop->id == $result['id'];
            });

            $existingResult->calculated_result = $result['calculated_result'];
            $existingResult->save();
        }
    }

    /**
     * Mark all the previous submissions as unconfirmed.
     *
     * Ignoring the new submission here since it will get updated anyway.
     *
     * @param int $charonId
     * @param int $newId
     * @param int $studentId
     */
    private function unconfirmOldSubmissions(int $charonId, int $newId, int $studentId) {
        $submissions = $this->submissionsRepository->findConfirmedSubmissionsForUserAndCharon(
            $studentId,
            $charonId
        );

        foreach ($submissions as $oldSubmission) {
            if ($oldSubmission->id === $newId) {
                continue;
            }

            $this->submissionsRepository->unconfirmSubmission($oldSubmission);
        }
    }
}
