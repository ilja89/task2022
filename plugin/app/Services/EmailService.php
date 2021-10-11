<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\EmailRepository;
use Zeizig\Moodle\Globals\User;

class EmailService
{
    /** @var EmailRepository */
    private $emailRepository;

    /**
     * @param EmailRepository $emailRepository
     */
    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    public function sendEmailFromTeacherToStudent($student_id, $email_subject, $message_text, $message_html) {
        $teacher_id = app(User::class)->currentUserId();
        $this->emailRepository->sendEmailFromTeacherToStudent($teacher_id, $student_id, $email_subject, $message_text, $message_html);
    }
}