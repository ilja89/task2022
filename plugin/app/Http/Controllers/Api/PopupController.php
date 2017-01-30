<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\CharonRepository;
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

    /**
     * PopupController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     */
    public function __construct(Request $request, CharonRepository $charonRepository)
    {
        $this->charonRepository = $charonRepository;
        $this->request = $request;
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
}
