<?php

namespace TTU\Charon\Services\Flows;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;

/**
 * @version Registration 2.*
 */
class BookStudentRegistration
{
    /** @var FindAvailableRegistrationTimes */
    private $findRegistrationTimes;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var LabRepository */
    private $labRepository;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @param FindAvailableRegistrationTimes $findRegistrationTimes
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $registrationRepository
     * @param LabRepository $labRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        FindAvailableRegistrationTimes $findRegistrationTimes,
        SubmissionsRepository $submissionsRepository,
        DefenseRegistrationRepository $registrationRepository,
        LabRepository $labRepository,
        UserRepository $userRepository
    ) {
        $this->findRegistrationTimes = $findRegistrationTimes;
        $this->submissionsRepository = $submissionsRepository;
        $this->registrationRepository = $registrationRepository;
        $this->labRepository = $labRepository;
        $this->userRepository = $userRepository;
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

        try {
            DB::beginTransaction();
            $this->registrationRepository->lock(true);

            $registrationIds = $this->findAvailableTimes($labId, $submissions->first()->charon, $start, $end);

            if (empty($registrationIds)) {
                return false;
            }

            $this->bookTimes($registrationIds, $studentId, $charonId, $submissionId);

            $this->scheduleBookingRelease($registrationIds);

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
     * @param int $labId
     * @param Charon $charon
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return array
     */
    private function findAvailableTimes(int $labId, Charon $charon, Carbon $start, Carbon $end): array
    {
        /**
         * Easy solution:
         * - find only where lab_id is $labId and time between $start and $end
         * - find first x subsequent timeslots for the same teacher where x is how many 5min slots it is required to fit charon defense duration
         */

        /**
         * Moderate solution:
         * - identify other bookings/registrations by the student in the same time range and try to match x slots next to those
         */

        /**
         * Difficult solution:
         * - move existing registrations around to either fill in gaps causes by cancellations or widen gaps to fit x if possible within the range
         * - alternatively filling gaps ought to be scheduled as a cron job after cancellations happen to reduce time spent during this booking request
         */

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

    /**
     * @param array $registrationIds
     */
    private function scheduleBookingRelease(array $registrationIds)
    {
        // TODO: Create a configuration to store how many minutes (default to 15m) a booking can be active before it is cancelled,
        // TODO: schedule a cron job +15min to the future to clear up registrations which are in booking state and
        // updated_at more than 15min ago.
    }
}
