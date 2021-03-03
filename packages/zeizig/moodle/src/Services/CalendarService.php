<?php


namespace Zeizig\Moodle\Services;


/**
 * Class CalendarService.
 *
 * A service to interact with Moodle's Calendar API.
 *
 * More documentation here: https://docs.moodle.org/dev/Calendar_API
 *
 * @package Zeizig\Moodle\Services
 */
class CalendarService
{
    /**
     * Create an event in the calendar.
     *
     * @param string $eventType
     * @param string $name
     * @param string $description
     * @param int $courseId
     * @param string $moduleName
     * @param int $instanceId
     * @param $timeOpen
     * @param bool $isVisible
     * @param bool $isAction
     * @param int $groupId
     * @param int $timeDuration
     *
     * @return
     */
    public function createEvent(
        $eventType,
        $name,
        $description,
        $courseId,
        $moduleName,
        $instanceId,
        $timeOpen,
        $isVisible,
        $isAction = false,
        $groupId = 0,
        $timeDuration = 0
    ) {
        global $CFG;
        require_once($CFG->dirroot . '/calendar/lib.php');

        $event = new \stdClass();
        $event->eventtype = $eventType;
        $event->type = $isAction ? \CALENDAR_EVENT_TYPE_ACTION : \CALENDAR_EVENT_TYPE_STANDARD;
        $event->name = $name;
        $event->description = $description;
        $event->courseid = $courseId;
        $event->groupid = $groupId;
        $event->userid = 0;
        $event->modulename = $moduleName;
        $event->instance = $instanceId;
        $event->timestart = $timeOpen;
        $event->visible = $isVisible;
        $event->timeduration = $timeDuration;

        return \calendar_event::create($event);
    }
}