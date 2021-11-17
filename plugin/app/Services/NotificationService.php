<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use stdClass;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Globals\User;

/**
 * Class NotificationService.
 *
 * @package TTU\Charon\Services
 */
class NotificationService
{
    /**
     * @var SubmissionsRepository
     */
    private $submissionsRepository;

    /**
     * NotificationService constructor.
     */
    public function __construct(SubmissionsRepository $submissionsRepository)
    {
        $this->submissionsRepository = $submissionsRepository;
    }

    /**
     * Send a notification to student about a new comment submission.
     *
     * @param int $submissionId
     * @param string $messageText
     * @param Charon $charon
     * @param string $filePath
     */
    public function sendNotificationToStudent(
        int $submissionId,
        string $messageText,
        Charon $charon,
        string $filePath
    ) {
        $teacher = app(User::class)->currentUser();

        $submission = $this->submissionsRepository->find($submissionId);

        $students = $this->submissionsRepository->findAllUsersAssociated($submissionId);

        $cm_id = $charon->courseModule()->id;
        $url = '/mod/charon/view.php?id=' . $cm_id;

        $messageText = htmlspecialchars($messageText);
        $messageText = str_replace( "\n", '<br />', $messageText );

        $messageTextHtml = <<<EOT
<h4>$charon->name</h4><br>
<b>You've got a new comment for the submission that was submitted at 
$submission->created_at</b><br>
<b>Author: $teacher->firstname $teacher->lastname</b><br>
<b>File that was commented: $filePath</b><br><br>
<p style="white-space: pre-wrap">$messageText</p>
EOT;

        foreach ($students as $student) {
            $this->sendNotification(
                $teacher,
                $student,
                'comment',
                "New comment: " . $charon->name,
                $messageText,
                $messageTextHtml,
                $url,
                $charon->name
            );
        }
    }

    /**
     * Send a notification to user.
     *
     * @param \Zeizig\Moodle\Models\User $userFrom
     * @param stdClass $userTo
     * @param string $notificationName
     * @param string $notificationSubject
     * @param string $messageText
     * @param string $messageTextHtml
     * @param string $urlToDirectTo
     * @param string $urlNameToDirect
     */
    private function sendNotification(
        \Zeizig\Moodle\Models\User $userFrom,
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
