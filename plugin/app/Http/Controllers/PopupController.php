<?php

namespace TTU\Charon\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Repositories\PluginConfigRepository;
use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers
 */
class PopupController extends Controller
{

    /** @var PluginConfigRepository */
    protected $pluginConfigRepository;

    public function __construct(
        Request $request,
        PluginConfigRepository $pluginConfigRepository
    )
    {
        parent::__construct($request);
        $this->pluginConfigRepository = $pluginConfigRepository;
    }

    /**
     * Display the Charon popup.
     *
     * @param Course $course
     *
     * @return Factory|View
     * @throws Exception
     */
    public function index(Course $course)
    {
        $this->setUrl($course->id);

        return view('popup.index', compact('course'));
    }

    public function getReleaseDate() : string
    {
        return "date";
        /*$version = $this->pluginConfigRepository->getMoodleVersion();
        return substr($version, 0, 4) . '-' . substr($version, 4, 2) . '-' .
            substr($version, 6, 2);*/
    }

    /**
     * Sets the URL. Needed by Moodle.
     *
     * @param  integer  $courseId
     */
    private $test;
    private function setUrl($courseId)
    {
        global $PAGE;
        $PAGE->set_url('/mod/charon/courses/' . $courseId . '/popup', []);
    }


}
