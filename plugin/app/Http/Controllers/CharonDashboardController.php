<?php


namespace TTU\Charon\Http\Controllers;


use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use Zeizig\Moodle\Models\Course;

/**
 * Class CharonDashboardController.
 *
 * @package TTU\Charon\Http\Controllers
 */
class CharonDashboardController extends Controller
{
    /**
     * Display the Charon dashboard
     *
     * @param Course $course
     * @param Charon $charon
     *
     * @returns Factory|View
     */
    public function index(Course $course, Charon $charon)
    {
        $this->setUrl($course->id, $charon->id);

        return view('charon_dashboard.index', [
            'course' => $course,
            'charon' => $charon
        ]);
    }

    /**
     * Sets the URL. Needed by Moodle.
     *
     * @param integer $courseId
     * @param integer $charonId
     */
    private function setUrl(int $courseId, int $charonId)
    {
        global $PAGE;
        $PAGE->set_url('/mod/charon/courses/' . $courseId . '/charons/' . $charonId . '/dashboard', []);
    }


}