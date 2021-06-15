<?php

namespace TTU\Charon\Tasks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\DefenseRegistrationRepository;

class ExpireBookedRegistrations implements AdhocTask
{
    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /**
     * @param DefenseRegistrationRepository $registrationRepository
     */
    public function __construct(DefenseRegistrationRepository $registrationRepository) {
        $this->registrationRepository = $registrationRepository;
    }

    /**
     * @param mixed $arguments empty
     */
    public function execute($arguments)
    {
        $expirationMinutes = Config::get('app.defense_booking_minutes');

        $affected = $this->registrationRepository
            ->query()
            ->where('progress', 'Booked')
            ->where('updated_at', '<', Carbon::now()->minutes(-$expirationMinutes))
            ->update([
                'student_id' => null,
                'charon_id' => null,
                'submission_id' => null,
                'progress' => 'New'
            ]);

        if ($affected < 1) {
            return;
        }

        $message = $affected == 1
            ? '%s booked registration was older than %s minutes, marked it as expired'
            : '%s booked registrations were older than %s minutes, marked them as expired';

        Log::info(sprintf($message, $affected, $expirationMinutes));
    }
}
