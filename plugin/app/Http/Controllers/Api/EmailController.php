<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Services\EmailService;

class EmailController extends Controller
{
    /** @var EmailService */
    private $emailService;

    /**
     * @param Request $request
     * @param EmailService $emailService
     */
    public function __construct(Request $request, EmailService $emailService)
    {
        parent::__construct($request);
        $this->emailService = $emailService;
    }

    public function sendEmailFromTeacherToStudent() {
        $this->emailService->sendEmailFromTeacherToStudent(
            $this->request->input('student_id'),
            $this->request->input('subject'),
            $this->request->input('message_text'),
            $this->request->input('message_html')
        );
    }
}