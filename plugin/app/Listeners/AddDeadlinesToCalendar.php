<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Models\Deadline;
use Zeizig\Moodle\Services\CalendarService;

class AddDeadlinesToCalendar
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
     * @param  CharonCreated  $event
     * @return void
     */
    public function handle(CharonCreated $event)
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

            $event = $this->calendarService->createEvent(
                'CHARON_DEADLINE',
                $name,
                $description,
                $charon->course,
                config('moodle.plugin_slug'),
                $charon->id,
                $deadline->deadline_time->getTimestamp(),
                true,
                true
            );

            $deadline->event_id = $event->id;
            $deadline->save();
        });
    }
}
