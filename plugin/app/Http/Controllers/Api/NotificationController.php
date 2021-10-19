<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\NotificationService;

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

    public function sendNotificationFromTeacherToStudent() {
        $this->notificationService->sendNotificationFromTeacherToStudent(
            $this->request->input('student_id'),
            $this->request->input('subject'),
            $this->request->input('message_text'),
            $this->request->input('file_path'),
            $this->request->input('charon_id')
        );
    }
}