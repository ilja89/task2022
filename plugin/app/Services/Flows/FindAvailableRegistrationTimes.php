<?php

namespace TTU\Charon\Services\Flows;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\DefenseRegistration;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
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

    /** @var LabTeacherRepository */
    private $teacherRepository;

    /**
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $registrationRepository
     * @param LabRepository $labRepository
     * @param LabTeacherRepository $teacherRepository
     */
    public function __construct(
        SubmissionsRepository $submissionsRepository,
        DefenseRegistrationRepository $registrationRepository,
        LabRepository $labRepository,
        LabTeacherRepository $teacherRepository
    ) {
        $this->submissionsRepository = $submissionsRepository;
        $this->registrationRepository = $registrationRepository;
        $this->labRepository = $labRepository;
        $this->teacherRepository = $teacherRepository;
    }

    /**
     * Find which registration times are available for the student
     *
     * TODO: make or update a ticket which creates bookings, free expired bookings should be scheduled there as a cron job
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
        $submissions = $this->filter($studentId, $submissions, $start, $end);

        if ($submissions->isEmpty()) {
            return [];
        }

        $this->validate($courseId, $studentId, $submissions);

        $charons = $submissions->mapWithKeys(function ($submission) {
            return [$submission->charon->id => $submission->charon];
        });

        $labs = $this->findLabs($charons, $start, $end);

        if ($labs->isEmpty()) {
            return [];
        }

        $ownTeachers = $this->findOwnTeachers($studentId, $courseId);

        $timeslots = $this->findAvailableTimes($labs, $ownTeachers);

        if ($timeslots->isEmpty()) {
            return [];
        }

        return $this->group($labs, $timeslots, $charons, $ownTeachers);
    }

    /**
     * @param int $courseId
     * @param int $studentId
     * @param Collection $submissions
     *
     * @throws ValidationException
     */
    private function validate(int $courseId, int $studentId, Collection $submissions)
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
    private function filter(int $studentId, array $submissions, Carbon $start, Carbon $end): Collection
    {
        $registeredCharons = $this->registrationRepository->filterCharonsWithActiveStudentRegistrations(
            $studentId,
            array_keys($submissions)
        );

        return $this->submissionsRepository
            ->query()
            ->where('confirmed', 0)
            ->whereIn('id', array_values($submissions))
            ->with(['charon.grademaps', 'users', 'results'])
            ->get()
            ->filter(function (Submission $submission) use ($studentId, $start, $end, $registeredCharons) {
                $charon = $submission->charon;

                if (in_array($charon->id, $registeredCharons)) {
                    return false;
                }

                if ($charon->defense_start_time && $start->isBefore($charon->defense_start_time)) {
                    return false;
                }

                if ($charon->defense_deadline && $end->isAfter($charon->defense_deadline)) {
                    return false;
                }

                $gradeTypes = $charon->grademaps->pluck('grade_type_code');
                $results = $submission->results->filter(function (Result $result) use ($studentId) {
                    return $result->user_id = $studentId;
                });

                // TODO: verify if and how we can have multiple style grades?
                if ($gradeTypes->contains(101)) {
                    foreach ($results as $result) {
                        if ($result->grade_type_code > 100 && $result->grade_type_code <= 1000 && $result->calculated_result < 1) {
                            return false;
                        }
                    }
                }

                // TODO: verify if and how we can have multiple test grades?
                if ($gradeTypes->contains(1) && $charon->defense_threshold) {
                    $threshold = $charon->defense_threshold / 100;
                    foreach ($results as $result) {
                        if ($result->grade_type_code <=100 && $result->calculated_result < $threshold) {
                            return false;
                        }
                    }
                }

                return true;
            });
    }

    /**
     * @param Collection $charons
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection|Lab[]
     */
    private function findLabs(Collection $charons, Carbon $start, Carbon $end): Collection
    {
        return $this->labRepository->findLabsForCharons($charons->pluck('id')->all(), $start, $end);
    }

    /**
     * @param int $studentId
     * @param int $courseId
     * @return int[]
     */
    private function findOwnTeachers(int $studentId, int $courseId): array
    {
        return $this->teacherRepository->getOwnTeachersIdsForStudent($studentId, $courseId);
    }

    /**
     * @param Collection|Lab[] $labs
     * @param int[] $ownTeachers
     *
     * @return Collection|DefenseRegistration[]
     */
    private function findAvailableTimes(Collection $labs, array $ownTeachers): Collection
    {
        return $this->registrationRepository->findAvailableTimesForStudent(
            $labs->pluck('id')->all(),
            $ownTeachers
        );
    }

    /**
     * TODO: when a charon is registered for a defense it will take up x amount of registration times depending
     * on the length of the defense time specified for the charon and divided by the timeslot length
     *
     * @param Collection|Lab[] $labs
     * @param Collection|DefenseRegistration[] $timeslots
     * @param Collection|Charon[] $charons
     * @param int[] $ownTeachers
     *
     * @return array
     */
    private function group(Collection $labs, Collection $timeslots, Collection $charons, array $ownTeachers): array
    {
        $labTimeslots = $timeslots->mapToGroups(function ($timeslot) {
            return [$timeslot->lab_id => $timeslot];
        });

        $result = [];

        foreach ($labs as $lab) {
            if (!$labTimeslots->has($lab->id)) {
                continue;
            }

            $labCharons = collect($lab->charons)
                ->mapToGroups(function ($id) use ($charons) {
                    $charon = $charons->get($id);

                    // TODO: currently it is not clearly defined how to check "if a charon can be defended only to own teacher", using temp
                    $key = $charon->choose_teacher == 1 ? 'any' : 'own';
                    return ['any' => $charon->id];
                });

            $chunks = [];

            foreach ($labTimeslots->get($lab->id) as $timeslot) {
                /** @var DefenseRegistration $timeslot */

                if (empty($labCharons->get('any')) && !in_array($timeslot->teacher_id, $ownTeachers)) {
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
                        'start' => $start,
                        'end' => $end->isBefore($lab->end) ? $end : $lab->end,
                        'charons' => [],
                        'times' => 0
                    ];
                }

                $chunks[$key]['times'] += 1;

                if ($labCharons->has('any')) {
                    $chunks[$key]['charons'] += $labCharons->get('any')->all();
                }

                if ($labCharons->has('own')) {
                    $chunks[$key]['charons'] += $labCharons->get('own')->all();
                }
            }

            $result = array_merge($result, array_values($chunks));
        }

        return $result;
    }
}
