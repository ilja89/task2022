<?php

namespace TTU\Charon\Repositories;

class NotificationRepository
{
    public function sendNotificationFromTeacherToStudent($teacherId, $studentId, $emailSubject, $message_text, $file_path, $charon_id) {

        $teacher = \DB::table('user')->where('id', $teacherId)
            ->first();

        $student = \DB::table('user')->where('id', $studentId)
            ->first();

        $charon = \DB::table('charon')->where('id', $charon_id)
            ->first();

        $message = new \core\message\message();
        $message->component = 'mod_charon'; // Your plugin's name
        $message->name = 'comment'; // Your notification name from message.php
        $message->userfrom = $teacher; // If the message is 'from' a specific user you can set them here
        $message->userto = $student;
        $message->subject = $emailSubject;
        $message->fullmessage = $message_text;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = <<<EOT
<h4>$charon->name</h4><br>
<b>$teacher->firstname $teacher->lastname</b><br>
<b>$file_path</b><br>
$message_text
EOT;
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
        $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        $message->set_additional_content('email', $content);

        message_send($message);
    }
}