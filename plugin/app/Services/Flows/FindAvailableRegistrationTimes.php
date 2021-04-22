<?php

namespace TTU\Charon\Services\Flows;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\DefenseRegistration;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Validators\RegistrationValidator;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

/**
 * @version Registration 2.*
 */
class FindAvailableRegistrationTimes
{
    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /**
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $registrationRepository
     */
    public function __construct(
        SubmissionsRepository $submissionsRepository,
        DefenseRegistrationRepository $registrationRepository
    ) {
        $this->submissionsRepository = $submissionsRepository;
        $this->registrationRepository = $registrationRepository;
    }

    /**
     * Find which registration times are available for the student
     *
     * TODO: make or update a ticket which creates bookings, free expired bookings should be scheduled there as a cron job
     *
     * @param Course $course
     * @param int $studentId
     * @param array $submissions [charon_id => submission_id]
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     * @throws ValidationException
     */
    public function run(Course $course, int $studentId, array $submissions, Carbon $start, Carbon $end): array
    {
        $submissions = $this->filter($studentId, $submissions, $start, $end);

        if ($submissions->isEmpty()) {
            return [];
        }

        $this->validate($course, $studentId, $submissions);

        $charons = $submissions->map(function ($submission) {
            return $submission->charon();
        });

        $labs = $this->findLabs($charons, $start, $end);

        if ($labs->isEmpty()) {
            return [];
        }

        $ownTeachers = $this->findOwnTeachers($studentId);

        $times = $this->findAvailableTimes($labs, $start, $end, $ownTeachers);

        if ($times->isEmpty()) {
            return [];
        }

        $times = $this->attachCharons($times, $charons, $ownTeachers);

        return $this->group($times);
    }

    /**
     * @param Course $course
     * @param int $studentId
     * @param Collection $submissions
     *
     * @throws ValidationException
     */
    private function validate(Course $course, int $studentId, Collection $submissions)
    {
        app()->make(RegistrationValidator::class)
            ->studentBelongsToCourse($course, $studentId)
            ->submissionsBelongToCourse($course, $submissions)
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

                if ($registeredCharons->contains($charon->id)) {
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
        return Lab::all();
    }

    /**
     * @param int $studentId
     *
     * @return Collection|User[]
     */
    private function findOwnTeachers(int $studentId)
    {
        return User::all();
    }

    /**
     * @param Collection|Lab[] $labs
     * @param Carbon $start
     * @param Carbon $end
     * @param Collection|User[] $ownTeachers
     *
     * @return Collection|DefenseRegistration[]
     */
    private function findAvailableTimes(Collection $labs, Carbon $start, Carbon $end, Collection $ownTeachers): Collection
    {
        return DefenseRegistration::all();
    }

    /**
     * @param Collection|DefenseRegistration[] $times
     * @param Collection|Charon[] $charons
     * @param Collection|User[] $ownTeachers
     *
     * @return array
     */
    private function attachCharons(Collection $times, Collection $charons, Collection $ownTeachers): array
    {
        return [];
    }

    /**
     * @param array $times
     *
     * @return array
     */
    private function group(array $times): array
    {
        return [];
    }
}
