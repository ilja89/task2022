<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\PlagiarismCheck;
use TTU\Charon\Services\PlagiarismService;

/**
 * Class PlagiarismCallbackController.
 * Handles accepting plagiarism checksuites from the plagiarism app.
 *
 * @package TTU\Charon\Http\Controllers
 */
class PlagiarismCallbackController extends Controller
{
    /** @var PlagiarismService */
    private $plagiarismService;

    /**
     * PlagiarismCallbackController constructor.
     *
     * @param Request $request
     * @param PlagiarismService $plagiarismService
     */
    public function __construct(
        Request $request,
        PlagiarismService $plagiarismService
    ) {
        parent::__construct($request);
        $this->plagiarismService = $plagiarismService;
    }

    /**
     * Accepts plagiarism checks from the plagiarism app.
     *
     * @param PlagiarismCheck $plagiarismCheck
     */
    public function index(PlagiarismCheck $plagiarismCheck)
    {
        Log::info("Plagiarism check result ", [json_decode($this->request->getContent(), true), $plagiarismCheck]);

        $this->plagiarismService->updateCheck($plagiarismCheck, json_decode($this->request->getContent(), true));
    }
}
