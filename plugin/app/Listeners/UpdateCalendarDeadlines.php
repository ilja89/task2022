<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\CharonUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use TTU\Charon\Models\Deadline;
use Zeizig\Moodle\Models\Event;
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
        $eventIds = $event->oldDeadlineEventIds;

        Event::destroy($eventIds);

        $charon = $event->charon;

        $previousPercentage = 100;
        $charon->deadlines->each(function ($deadline) use ($charon, &$previousPercentage) {
            /** @var Deadline $deadline */
            $event = $this->calendarService->createEvent(
                'CHARON_DEADLINE',
                "{$charon->name} - {$previousPercentage}%",
                $charon->description,
                $charon->course,
                config('moodle.plugin_slug'),
                $charon->id,
                $deadline->deadline_time->getTimestamp(),
                true,
                true
            );

            $deadline->event_id = $event->id;
            $deadline->save();

            $previousPercentage = $deadline->percentage;
        });
    }
}
