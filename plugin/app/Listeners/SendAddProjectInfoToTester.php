<?php

namespace TTU\Charon\Listeners;

use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Repositories\CourseSettingsRepository;
use TTU\Charon\Services\TesterCommunicationService;

class SendAddProjectInfoToTester
{
    /** @var TesterCommunicationService */
    private $testerCommunicationService;

    /** @var CourseSettingsRepository */
    private $courseSettingsRepository;

    /**
     * Create the event listener.
     *
     * @param TesterCommunicationService $testerCommunicationService
     * @param CourseSettingsRepository $courseSettingsRepository
     */
    public function __construct(TesterCommunicationService $testerCommunicationService, CourseSettingsRepository $courseSettingsRepository)
    {
        $this->testerCommunicationService = $testerCommunicationService;
        $this->courseSettingsRepository = $courseSettingsRepository;
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $courseId       = $event->charon->course;
        $courseSettings = $this->courseSettingsRepository->getCourseSettingsByCourseId($courseId);
        $this->testerCommunicationService->sendAddProjectInfo(
            $event->charon,
            $courseSettings->unittests_git,
            $event->charon->moodleCourse->shortname
        );
    }
}
