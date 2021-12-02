<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use stdClass;
use Zeizig\Moodle\Models\User;

/**
 * Class NotificationService.
 *
 * @package TTU\Charon\Services
 */
class NotificationService
{

    /**
     * Send a notification to user.
     *
     * @param User $userFrom
     * @param stdClass $userTo
     * @param string $notificationName
     * @param string $notificationSubject
     * @param string $messageText
     * @param string $messageTextHtml
     * @param string $urlToDirectTo
     * @param string $urlNameToDirect
     */
    public function sendNotification(
        User $userFrom,
        stdClass $userTo,
        string $notificationName,
        string $notificationSubject,
        string $messageText,
        string $messageTextHtml,
        string $urlToDirectTo,
        string $urlNameToDirect
    ) {
        $contextUrl = (new \moodle_url($urlToDirectTo))->out(false);
        $emailFooter = <<<EOT
<p><a href="$contextUrl">Go to: $urlNameToDirect</a></p>
EOT;
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
        $message->contexturl = $contextUrl; // A relevant URL for the notification
        $message->contexturlname = $urlNameToDirect; // Link title explaining where users get to for the contexturl
        $content = array('*' => array('footer' => $emailFooter)); // Extra content for specific processor
        $message->set_additional_content('email', $content);

        $messageId = message_send($message);

        if (!$messageId) {
            Log::warning("Notification was not delivered");
        }
    }
}
