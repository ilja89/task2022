<?php

namespace TTU\Charon\Services\Flows;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Models\DefenseRegistration;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Validators\RegistrationValidator;

/**
 * @version Registration 2.*
 */
class FindAvailableRegistrationTimes
{
    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var LabRepository */
    private $labRepository;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $registrationRepository
     * @param LabRepository $labRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        SubmissionsRepository $submissionsRepository,
        DefenseRegistrationRepository $registrationRepository,
        LabRepository $labRepository,
        UserRepository $userRepository
    ) {
        $this->submissionsRepository = $submissionsRepository;
        $this->registrationRepository = $registrationRepository;
        $this->labRepository = $labRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Find which registration times are available for the student
     *
     * @param int $courseId
     * @param int $studentId
     * @param array $submissions [charon_id => submission_id]
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     * @throws ValidationException
     */
    public function run(int $courseId, int $studentId, array $submissions, Carbon $start, Carbon $end): array
    {
        $submissions = $this->filterValidSubmissions($studentId, $submissions, $start, $end);

        if ($submissions->isEmpty()) {
            return [];
        }

        $this->validate($courseId, $studentId, $submissions);

        $charons = $submissions->map(function ($submission) {
            return $submission->charon->id;
        })->unique()->all();

        $labs = $this->findLabs($charons, $start, $end, $studentId);

        if ($labs->isEmpty()) {
            return [];
        }

        $timeslots = $this->findAvailableTimes($labs);

        if ($timeslots->isEmpty()) {
            return [];
        }

        return $this->makeChunks($labs, $timeslots);
    }

    /**
     * @param int $courseId
     * @param int $studentId
     * @param Collection $submissions
     *
     * @throws ValidationException
     */
    public function validate(int $courseId, int $studentId, Collection $submissions)
    {
        app()->make(RegistrationValidator::class)
            ->studentBelongsToCourse($courseId, $studentId)
            ->submissionsBelongToCourse($courseId, $submissions)
            ->submissionsBelongToStudent($studentId, $submissions)
            ->validate();
    }

    /**
     * Remove the following Charons/Submissions:
     * - already received a confirmed grade
     * - have active registrations
     * - out of defensible time range
     * - results are below grade threshold
     *
     * @param int $studentId
     * @param array $submissions
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection|Submission[]
     */
    public function filterValidSubmissions(int $studentId, array $submissions, Carbon $start, Carbon $end): Collection
    {
        $registeredCharons = $this->registrationRepository->filterCharonsWithActiveStudentRegistrations(
            $studentId,
            array_keys($submissions)
        );

        return $this->submissionsRepository
            ->query()
            ->where('confirmed', 0)
            ->whereIn('id', array_values($submissions))
            ->with(['charon', 'users', 'results'])
            ->get()
            ->filter(function (Submission $submission) use ($studentId, $start, $end, $registeredCharons) {
                $charon = $submission->charon;

                if (in_array($charon->id, $registeredCharons)) {
                    return false;
                }

                if ($charon->defense_start_time && $charon->defense_start_time->isAfter($start)) {
                    return false;
                }

                if ($charon->defense_deadline && $charon->defense_deadline->isBefore($end)) {
                    return false;
                }

                $results = $submission->results->filter(function (Result $result) use ($studentId) {
                    return $result->user_id = $studentId;
                });

                $testThreshold = $charon->defense_threshold / 100;

                foreach ($results as $result) {
                    if ($result->isStyleGrade()) {
                        if ($result->calculated_result < 1) {
                            return false;
                        }
                    } else if ($result->isTestsGrade()) {
                        if ($result->calculated_result < $testThreshold) {
                            return false;
                        }
                    }
                }

                return true;
            });
    }

    /**
     * @param array $charons
     * @param Carbon $start
     * @param Carbon $end
     * @param int $studentId
     *
     * @return Collection|Lab[]
     */
    private function findLabs(array $charons, Carbon $start, Carbon $end, int $studentId): Collection
    {
        $labs = $this->labRepository->findLabsForCharons($charons, $start, $end);
        $labs->load('groups');

        $containsGroupRestriction = $labs->contains(function ($lab) {
            return $lab->groups->count() > 0;
        });

        if (!$containsGroupRestriction) {
            return $labs;
        }

        $studentGroups = $this->userRepository->userGroups($studentId);

        return $labs->filter(function ($lab) use ($studentGroups) {
            $labGroups = $lab->groups->pluck('id')->all();
            if (empty($labGroups)) {
                return true;
            }

            return !empty(array_intersect($studentGroups, $labGroups));
        });
    }

    /**
     * @param Collection|Lab[] $labs
     *
     * @return Collection|DefenseRegistration[]
     */
    private function findAvailableTimes(Collection $labs): Collection
    {
        return $this->registrationRepository->findAvailableTimes($labs->pluck('id')->all());
    }

    /**
     * @param Collection|Lab[] $labs
     * @param Collection|DefenseRegistration[] $timeslots
     *
     * @return array
     */
    private function makeChunks(Collection $labs, Collection $timeslots): array
    {
        $labTimeslots = $timeslots->mapToGroups(function ($timeslot) {
            return [$timeslot->lab_id => $timeslot];
        });

        $result = [];

        foreach ($labs as $lab) {
            if (!$labTimeslots->has($lab->id)) {
                continue;
            }

            $chunks = [];

            foreach ($labTimeslots->get($lab->id) as $timeslot) {
                /** @var DefenseRegistration $timeslot */

                if (empty($lab->charons)) {
                    continue;
                }

                $diffInMinutes = floor(($timeslot->time->timestamp - $lab->start->timestamp) / 60);
                $closestChunkStart = floor($diffInMinutes / $lab->chunk_size) * $lab->chunk_size;
                $start = $lab->start->copy()->addMinutes($closestChunkStart);
                $key = $start->toString();

                if (!isset($chunks[$key])) {
                    $end = $start->copy()->addMinutes($lab->chunk_size);
                    $chunks[$key] = [
                        'lab' => $lab->id,
                        'lab_name' => $lab->name,
                        'start' => $start,
                        'end' => $end->isBefore($lab->end) ? $end : $lab->end,
                        'charons' => $lab->charons,
                        'times' => 0
                    ];
                }

                $chunks[$key]['times'] += 1;
            }

            $result = array_merge($result, array_values($chunks));
        }

        return $result;
    }
}
