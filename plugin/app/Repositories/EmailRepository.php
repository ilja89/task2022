<?php

namespace TTU\Charon\Repositories;

class EmailRepository
{
    public function sendEmailFromTeacherToStudent($teacherId, $studentId, $emailSubject, $message_text, $message_html) {
//        global $CFG;
//        require_once ($CFG->dirroot . '/lib/moodlelib.php');
//
//        $CFG->smtphosts = 'smtp.mailtrap.io';
//        $CFG->smtpuser = '5aa9e89bfe8308';
//        $CFG->smtppass = '7a2708d228091e';

        $teacher = \DB::table('user')->where('id', $teacherId)
            ->first();

        $student = \DB::table('user')->where('id', $studentId)
            ->first();

        if(!\core_message\api::get_conversation_between_users([$teacherId, $studentId ])){
            $conversation = \core_message\api::create_conversation(
                \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL,
                [
                    $teacherId,
                    $studentId
                ]
            );
        }

        $message = new \core\message\message();
        $message->component = 'mod_charon'; // Your plugin's name
        $message->name = 'comment'; // Your notification name from message.php
        $message->userfrom = $teacher; // If the message is 'from' a specific user you can set them here
        $message->userto = $student;
        $message->subject = $emailSubject;
        $message->fullmessage = $message_text;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = '<p>' . $message_text . '</p>';
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
        $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        $message->set_additional_content('email', $content);

        $messageid = message_send($message);


//        email_to_user($student, $teacher, $emailSubject, $message_text, $message_html, '', '', true);
    }
}