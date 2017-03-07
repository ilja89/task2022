<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Services\CharonGradingService;

/**
 * Class SubmissionsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class SubmissionsController extends Controller
{
    /** @var CharonGradingService */
    private $charonGradingService;

    /**
     * SubmissionsController constructor.
     *
     * @param CharonGradingService $charonGradingService
     */
    public function __construct(CharonGradingService $charonGradingService)
    {
        $this->charonGradingService = $charonGradingService;
    }

    /**
     * Get all outputs for given submission. Also includes outputs for
     * results.
     *
     * @param  Submission $submission
     *
     * @return array
     */
    public function getOutputs(Submission $submission)
    {
        $outputs = [];
        $outputs['submission']['stdout'] = $submission->stdout;
        $outputs['submission']['stderr'] = $submission->stderr;
        foreach ($submission->results as $result) {
            if ($result->getGrademap() === null) {
                // If has no grademap - result exists but no grademap
                continue;
            }

            $outputs['results'][$result->id]['stdout'] = $result->stdout;
            $outputs['results'][$result->id]['stderr'] = $result->stderr;
        }

        return $outputs;
    }

    /**
     * Find a submission by its id.
     *
     * @param  Charon  $charon
     * @param  int  $submissionId
     *
     * @return Submission
     */
    public function findById(Charon $charon, $submissionId)
    {
        $submission = Submission::with([
            'results' => function ($query) use ($charon) {
                // Only select results which have a corresponding grademap
                $query->whereIn('grade_type_code', $charon->getGradeTypes());
                $query->select(['id', 'submission_id', 'calculated_result', 'grade_type_code']);
            },
        ])
                                 ->where('id', $submissionId)
                                 ->where('charon_id', $charon->id)
                                 ->first([
                                     'id',
                                     'charon_id',
                                     'confirmed',
                                     'created_at',
                                     'git_hash',
                                     'git_timestamp',
                                     'user_id',
                                     'mail',
                                 ]);
        return $submission;
    }

    /**
     * Add a new empty submission to the given Charon. Empty means that all the
     * outputs are empty and all results are 0.
     *
     * @param Request $request
     * @param Charon $charon
     *
     * @return Submission
     */
    public function addNewEmpty(Request $request, Charon $charon)
    {
        /** @var Submission $submission */
        $submission = $charon->submissions()->create([
            'user_id' => $request['student_id'],
            'git_hash' => '',
            'git_timestamp' => Carbon::now(),
            'stdout' => 'Manually created by teacher',
        ]);

        foreach ($charon->grademaps as $grademap) {
            $submission->results()->create([
                'grade_type_code' => $grademap->grade_type_code,
                'percentage' => 0,
                'calculated_result' => 0,
            ]);
        }

        $this->charonGradingService->updateGradeIfApplicable($submission);

        return $submission;
    }
}
