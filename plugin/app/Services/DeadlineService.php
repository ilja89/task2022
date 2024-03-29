<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;

/**
 * Class DeadlineService.
 *
 * @package TTU\Charon\Services
 */
class DeadlineService
{
    /**
     * Create the deadline with the given parameters.
     * The deadline array is the array gotten from the charon from request.
     *
     * @param  Charon  $charon
     * @param  array  $deadlineArray
     *
     * @return void
     */
    public function createDeadline(Charon $charon, $deadlineArray)
    {
        if (!$this->correctDeadline($deadlineArray)) {
            Log::info("invalid deadline: ", [$deadlineArray]);
            return;
        }

        $dateFormat = strlen(explode( '-',explode( ' ',$deadlineArray['deadline_time'])[0])[0]) == 4 ? 'Y-m-d H:i' : 'd-m-Y H:i';
        Log::info("Creating a deadline: ", [$deadlineArray]);
        $deadlineTime = Carbon::createFromFormat($dateFormat, $deadlineArray['deadline_time']);
        $charon->deadlines()->save(new Deadline([
            'deadline_time' => $deadlineTime,
            'percentage' => $deadlineArray['percentage'],
            'group_id' => $deadlineArray['group_id'],
        ]));
    }

    /**
     * Checks if the given deadline is correct. If it isn't, this method will return false.
     *
     * @param  array  $deadline
     *
     * @return bool
     */
    private function correctDeadline($deadline)
    {
        return $deadline['deadline_time'] !== null && $deadline['deadline_time'] !== '' && is_numeric($deadline['percentage']);
    }
}
