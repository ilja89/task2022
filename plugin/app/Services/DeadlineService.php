<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
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
    public function createDeadline($charon, $deadlineArray)
    {
        if (!$this->correctDeadline($deadlineArray)) {
            return;
        }

        $deadlineTime = Carbon::createFromFormat('d-m-Y H:i', $deadlineArray['deadline_time'], config('app.timezone'));
        $deadlineTime->setTimezone('UTC');
        $charon->deadlines()->save(new Deadline([
            'deadline_time' => $deadlineTime,
            'percentage' => $deadlineArray['percentage'],
            // TODO: Set group id.
//                'group_id' => $deadline['group_id']
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
