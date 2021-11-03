<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use stdClass;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Globals\User;

class NotificationService
{

    /**
     * Send a notification to student about a new comment submission.
     *
     * @param integer $studentId
     * @param string $notificationSubject
     * @param string $messageText
     * @param string $filePath
     * @param Charon $charon
     */
    public function sendNotificationFromTeacherToStudent(
        int $studentId,
        string $notificationSubject,
        string $messageText,
        string $filePath,
        Charon $charon
    ) {
        $teacherId = app(User::class)->currentUserId();

        $teacher = \DB::table('user')->where('id', $teacherId)
            ->first();

        $student = \DB::table('user')->where('id', $studentId)
            ->first();

        $cm_id = $charon->courseModule()->id;
        $url = '/mod/charon/view.php?id=' . $cm_id;

        $messageTextHtml = <<<EOT
                                        <h4><b>$charon->name</b></h4>
                                        <b>Comment auhtor: $teacher->firstname $teacher->lastname</b><br>
                                        <b>File that was commented: $filePath</b><br>
                                        <pre>$messageText</pre>
EOT;
        $this->sendNotification(
            $teacher,
            $student,
            'comment',
            "New comment for " . $notificationSubject,
            $messageText,
            $messageTextHtml,
            $url,
            $notificationSubject
        );
    }

    /**
     * Send a notification to user.
     *
     * @param stdClass $userFrom
     * @param stdClass $userTo
     * @param string $notificationName
     * @param string $notificationSubject
     * @param string $messageText
     * @param string $messageTextHtml
     * @param string $urlToDirectTo
     * @param string $urlNameToDirect
     */
    private function sendNotification(
        stdClass $userFrom,
        stdClass $userTo,
        string $notificationName,
        string $notificationSubject,
        string $messageText,
        string $messageTextHtml,
        string $urlToDirectTo,
        string $urlNameToDirect
    ) {
        $message = new \core\message\message();
        $message->component = 'mod_charon'; // Your plugin's name
        $message->name = $notificationName; // Your notification name from message.php
        $message->userfrom = $userFrom; // If the message is 'from' a specific user you can set them here
        $message->userto = $userTo;
        $message->subject = $notificationSubject;
        $message->fullmessage = $messageText;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $messageTextHtml;
        $message->smallmessage = $notificationSubject;
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url($urlToDirectTo))->out(false); // A relevant URL for the notification
        $message->contexturlname = $urlNameToDirect; // Link title explaining where users get to for the contexturl
        $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        $message->set_additional_content('email', $content);

        $messageId = message_send($message);

        if (is_int($messageId)) {
            Log::info("Notification was delivered successfully");
        }
    }
}
