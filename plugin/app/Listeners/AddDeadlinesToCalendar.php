<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Deadline;
use Zeizig\Moodle\Services\CalendarService;

class AddDeadlinesToCalendar
{
    /** @var CalendarService */
    private $calendarService;

    /** @var MoodleConfig */
    private $moodleConfig;

    /**
     * Create the event listener.
     *
     * @param CalendarService $calendarService
     */
    public function __construct(CalendarService $calendarService, MoodleConfig $moodleConfig)
    {
        $this->calendarService = $calendarService;
        $this->moodleConfig = $moodleConfig;
    }

    /**
     * Handle the event.
     *
     * @param  CharonCreated  $event
     * @param  string $userTimezone
     * @return void
     */
    public function handle(CharonCreated $event, string $userTimezone)
    {
        $charon = $event->charon;

        $charon->deadlines->each(function ($deadline) use ($userTimezone, $charon) {
            /** @var Deadline $deadline */
            $charonName = $charon->name;
            $percentage = $deadline['percentage'];
            $name = "{$charonName} - {$percentage}%";

            $description = __('descriptions.descriptionStart') . ' ' . $charonName
                . ' ' . __('descriptions.descriptionMiddle') . ' ' . $percentage
                . '% ' . __('descriptions.descriptionEnd');

            if ($userTimezone == "99") {
                $userTimezone = $this->moodleConfig->timezone;
            }

            $rightTime = $deadline->deadline_time->setTimezone($userTimezone);

            $event = $this->calendarService->createEvent(
                'CHARON_DEADLINE',
                $name,
                $description,
                $charon->course,
                config('moodle.plugin_slug'),
                $charon->id,
                $rightTime->getTimestamp() - $rightTime->getOffset(),
                true,
                true,
                $deadline->group_id
            );

            $deadline->event_id = $event->id;
            $deadline->save();
        });
    }
}
