<?php

namespace TTU\Charon\Repositories;

use Illuminate\Support\Facades\Log;
use Zeizig\Moodle\Models\User;

global $CFG;
require_once ($CFG->dirroot . '/lib/moodlelib.php');

class EmailRepository
{
    public function sendEmailFromTeacherToStudent($teacherId, $studentId, $emailSubject, $message_text, $message_html) {
        $teacher = User::where('id', $teacherId)
            ->first();

        $student = User::where('id', $studentId)
            ->first();

        email_to_user($student, $teacher, $emailSubject, $message_text, $message_html, '', '', true);
    }
}