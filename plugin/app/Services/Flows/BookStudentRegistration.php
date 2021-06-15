<?php

namespace TTU\Charon\Services\Flows;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Facades\MoodleCron;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Tasks\ExpireBookedRegistrations;

/**
 * @version Registration 2.*
 */
class BookStudentRegistration
{
    /** @var FindAvailableRegistrationTimes */
    private $findRegistrationTimes;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var MoodleCron */
    private $cron;

    /**
     * @param FindAvailableRegistrationTimes $findRegistrationTimes
     * @param DefenseRegistrationRepository $registrationRepository
     * @param MoodleCron $cron
     */
    public function __construct(
        FindAvailableRegistrationTimes $findRegistrationTimes,
        DefenseRegistrationRepository $registrationRepository,
        MoodleCron $cron
    ) {
        $this->findRegistrationTimes = $findRegistrationTimes;
        $this->registrationRepository = $registrationRepository;
        $this->cron = $cron;
    }

    /**
     * Book a Lab defense time for a student
     *
     * @param int $courseId
     * @param int $labId
     * @param int $studentId
     * @param int $charonId
     * @param int $submissionId
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return bool
     * @throws ValidationException
     */
    public function run(
        int $courseId,
        int $labId,
        int $studentId,
        int $charonId,
        int $submissionId,
        Carbon $start,
        Carbon $end
    ): bool {
        // TODO: should really find a proper way for building up these flows out of components
        $submissions = $this->findRegistrationTimes->filterValidSubmissions(
            $studentId,
            [$charonId => $submissionId],
            $start,
            $end
        );

        if ($submissions->isEmpty()) {
            return false;
        }

        $this->findRegistrationTimes->validate($courseId, $studentId, $submissions);

        // TODO: verify that the start-end time is a valid chunk for given lab and not manually modified time range

        try {
            DB::beginTransaction();
            $this->registrationRepository->lock(true);

            $registrationIds = $this->findAvailableTimes($labId, $submissions->first()->charon, $start, $end);

            if (empty($registrationIds)) {
                return false;
            }

            $this->bookTimes($registrationIds, $studentId, $charonId, $submissionId);

            $this->scheduleBookingRelease();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Failed to book a registration: ' . $exception->getMessage(), $exception->getTrace());
            throw $exception;
        } finally {
            DB::statement(DB::raw('UNLOCK TABLES'));
        }

        return true;
    }

    /**
     * Attempt to schedule a defense at the earliest time for a Teacher with the most available times in the range.
     * If the Charon defense takes up more than one timeslot, attempt to find subsequent timeslots at the same Teacher.
     *
     * TODO: fill in gaps in a timeslot caused by cancellations or expired bookings (at a separate ticket)
     *
     * TODO: identify other bookings/registrations by the student in the same time range and try to match x slots next
     * to those instead of the start of the range
     *
     * @param int $labId
     * @param Charon $charon
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     */
    private function findAvailableTimes(int $labId, Charon $charon, Carbon $start, Carbon $end): array
    {
        $timeslotDuration = Config::get('app.defense_timeslot_minutes');
        $requiredSlots = intval(ceil($charon->defense_duration / $timeslotDuration));

        $times = $this->registrationRepository->findAvailableTimesBetween($labId, $start, $end);

        $byTeacher = $times->mapToGroups(function ($time) {
            return [$time->teacher_id => $time];
        })->sortByDesc(function ($product) {
            return count($product);
        });

        if ($requiredSlots == 1) {
            return [$byTeacher->first()->first()->id];
        }

        foreach ($byTeacher as $times) {
            $found = [];
            $foundCount = 0;

            $times = $times->all();
            foreach ($times as $key => $time) {
                $found[] = $time->id;
                $foundCount++;

                if ($foundCount == $requiredSlots) {
                    return $found;
                }

                if (!isset($times[$key + 1])) {
                    break;
                }

                $nextTime = $times[$key + 1];
                if ($time->id + 1 != $nextTime->id) {
                    $found = [];
                    $foundCount = 0;
                }
            }
        }

        return [];
    }

    /**
     * @param array $registrationIds
     * @param int $studentId
     * @param int $charonId
     * @param int $submissionId
     */
    private function bookTimes(array $registrationIds, int $studentId, int $charonId, int $submissionId)
    {
        $this->registrationRepository->query()->whereIn('id', $registrationIds)->update([
            'student_id' => $studentId,
            'charon_id' => $charonId,
            'submission_id' => $submissionId,
            'progress' => 'Booked'
        ]);
    }

    private function scheduleBookingRelease()
    {
        $expirationTime = Config::get('app.defense_booking_minutes') * 60 + 5;
        $this->cron->enqueue(ExpireBookedRegistrations::class, [], $expirationTime);
    }
}
