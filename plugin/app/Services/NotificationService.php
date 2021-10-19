<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\NotificationRepository;
use Zeizig\Moodle\Globals\User;

class NotificationService
{
    /** @var NotificationRepository */
    private $notificationRepository;

    /**
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function sendNotificationFromTeacherToStudent($student_id, $notification_subject, $message_text, $file_path, $charon_id) {
        $teacher_id = app(User::class)->currentUserId();
        $this->notificationRepository->sendNotificationFromTeacherToStudent($teacher_id, $student_id, $notification_subject, $message_text, $file_path, $charon_id);
    }
}