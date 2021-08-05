<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\CharonUpdated;
use TTU\Charon\Models\Deadline;
use Zeizig\Moodle\Services\CalendarService;

class UpdateCalendarDeadlines
{
    /** @var CalendarService */
    private $calendarService;

    /**
     * Create the event listener.
     *
     * @param CalendarService $calendarService
     */
    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Handle the event.
     *
     * @param  CharonUpdated  $event
     * @return void
     */
    public function handle(CharonUpdated $event)
    {
        $charon = $event->charon;
        $charon->deadlines->each(function ($deadline) use ($charon) {
            /** @var Deadline $deadline */
            $charonName = $charon->name;
            $percentage = $deadline['percentage'];
            $name = "{$charonName} - {$percentage}%";
            $description = __('descriptions.descriptionStart') . ' ' . $charonName
                . ' ' . __('descriptions.descriptionMiddle') . ' ' . $percentage
                . '% ' . __('descriptions.descriptionEnd');
            $rightTime = $deadline->deadline_time;
            $rightTimeDuplicate = $deadline->deadline_time;
            $timeInRealZone = $rightTimeDuplicate->setTimezone(config('app.timezone'));
            $rightTimeOffset = $rightTime->getOffset() / 3600;
            $timeInRealZoneOffset = $timeInRealZone->getOffset() / 3600;
            $diffInHours = $timeInRealZoneOffset - $rightTimeOffset;
            $newTime = $rightTime->subHours($diffInHours);
            $event = $this->calendarService->createEvent(
                'CHARON_DEADLINE',
                $name,
                $description,
                $charon->course,
                config('moodle.plugin_slug'),
                $charon->id,
                $newTime->getTimestamp(),
                true,
                true,
                $deadline->group_id
            );

            $deadline->event_id = $event->id;
            $deadline->save();
        });
    }
}
