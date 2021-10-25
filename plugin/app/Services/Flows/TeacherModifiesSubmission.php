<?php

namespace TTU\Charon\Services\Flows;

use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use Zeizig\Moodle\Globals\User;

class TeacherModifiesSubmission
{
    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var User */
    private $user;

    /**
     * @param CharonGradingService $charonGradingService
     * @param SubmissionsRepository $submissionsRepository
     * @param User $user
     */
    public function __construct(
        CharonGradingService $charonGradingService,
        SubmissionsRepository $submissionsRepository,
        User $user
    ) {
        $this->charonGradingService = $charonGradingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->user = $user;
    }

    /**
     * When a teacher assigns a new result for a submission.
     *
     * Most common case is during defences and for defense grade,
     * but can also modify any grade result attached to the submission.
     *
     * @param Submission $submission
     * @param $newResults
     *
     * @return Submission
     */
    public function run(Submission $submission, $newResults)
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
