<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PopupController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /** @var Request */
    private $request;

    /** @var GrademapService */
    private $grademapService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /**
     * PopupController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param GrademapService $grademapService
     * @param CharonGradingService $charonGradingService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        GrademapService $grademapService,
        CharonGradingService $charonGradingService
    ) {
        $this->charonRepository = $charonRepository;
        $this->request = $request;
        $this->grademapService = $grademapService;
        $this->charonGradingService = $charonGradingService;
    }

    /**
     * Get Charons by course.
     *
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Charon[]
     */
    public function getCharonsByCourse(Course $course)
    {
        $charons = $this->charonRepository->findCharonsByCourse($course->id);

        foreach ($charons as $charon) {
            $charon->grademaps = Grademap::with('gradeItem')->where('charon_id', $charon->id)->get();
            $charon->deadlines = Deadline::where('charon_id', $charon->id)->get();
        }

        return $charons;
    }

    /**
     * @param Charon $charon
     *
     * @return \TTU\Charon\Models\Submission[]
     */
    public function getSubmissionsByCharon(Charon $charon)
    {
        $userId = $this->request['user_id'];

        return $this->charonRepository->findSubmissionsByCharonAndUser($charon->id, $userId);
    }

    /**
     * Saves the Submission results.
     *
     * @param  Charon $charon
     * @param  Submission $submission
     *
     * @return array
     */
    public function saveSubmission(Charon $charon, Submission $submission)
    {
        $newResults = $this->request['submission']['results'];

        foreach ($newResults as $result) {
            $existingResult = $this->getResultByIdFromArray($submission->results, $result['id']);

            $existingResult->calculated_result = $result['calculated_result'];
            $existingResult->save();
        }

        $this->charonGradingService->updateGradeIfApplicable($submission, true);
        $this->charonGradingService->confirmSubmission($submission);

        return [
            'status' => 'OK',
        ];
    }

    /**
     * @param  Result[] $results
     * @param  integer $id
     *
     * @return null|Result
     */
    private function getResultByIdFromArray($results, $id)
    {
        foreach ($results as $result) {
            if ($result->id == $id) {
                return $result;
            }
        }

        return null;
    }
}
