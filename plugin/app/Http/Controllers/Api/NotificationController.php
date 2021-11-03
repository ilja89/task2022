<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\NotificationService;
use Zeizig\Moodle\Globals\User;

class NotificationController extends Controller
{
    /** @var NotificationService */
    private $notificationService;

    /**
     * @param Request $request
     * @param NotificationService $notificationService
     */
    public function __construct(Request $request, NotificationService $notificationService)
    {
        parent::__construct($request);
        $this->notificationService = $notificationService;
    }

    /**
     * Send a notification to the student from teacher.
     *
     * @param Request $request
     * @param Charon $charon
     *
     * @return void
     */
    public function sendNotificationFromTeacherToStudent(Request $request, Charon $charon) {
        Log::info("Sending a submission comment notification to student with following parameters", [
            "studentId" => $request->input('student_id'),
            "teacherId" => app(User::class)->currentUserId(),
            "subject" => $request->input('subject'),
            "message text" => $request->input('message_text'),
            "file path" => $request->input('file_path'),
            "charonId" => $charon->name
        ]);
        $this->notificationService->sendNotificationFromTeacherToStudent(
            $request->input('student_id'),
            $request->input('subject'),
            $request->input('message_text'),
            $request->input('file_path'),
            $charon
        );
    }
}
