<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Services\PermissionsService;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;

/**
 * Class AssignmentController.
 * Used to display the assignment view.
 *
 * @package TTU\Charon\Http\Controllers
 */
class AssignmentController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /** @var Output */
    protected $output;

    /** @var Page */
    protected $page;

    /** @var PermissionsService */
    protected $permissionsService;

    /**
     * AssignmentController constructor.
     *
     * @param  CharonRepository  $charonRepository
     * @param  Output  $output
     * @param  Page  $page
     * @param  PermissionsService  $permissionsService
     */
    public function __construct(CharonRepository $charonRepository, Output $output, Page $page, PermissionsService $permissionsService)
    {
        $this->charonRepository = $charonRepository;
        $this->output         = $output;
        $this->page           = $page;
        $this->permissionsService    = $permissionsService;
    }

    /**
     * Render the assignment view where it shows one instance of the plugin.
     * This is the view shown to students.
     *
     * @param  Request  $request
     *
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $charon = $this->charonRepository->getCharonByCourseModuleId($request->get('id'));

        if (!$this->checkPermissions($charon)) {
            return null;
        }

        $this->addBreadcrumbs($charon);

        return view('assignment.index', [
            'header'   => $this->output->header(),
            'footer'   => $this->output->footer(),
            'instance' => $charon
        ]);
    }

    /**
     * Add breadcrumbs to the page.
     * Uses Moodle built in breadcrumbs.
     *
     * @param  Charon  $charon
     *
     * @return void
     */
    public function addBreadcrumbs($charon)
    {
        $courseModule = $charon->courseModule();

        $this->page->addBreadcrumb(
            $courseModule->moodleCourse->shortname,
            '/course/view.php?id=' . $courseModule->moodleCourse->id
        );
        $this->page->addBreadcrumb($charon->name);
    }

    /**
     * Check if the current user has permissions to see the assignment view. Will also redirect
     * if no permissions.
     *
     * When using this function check if returned value is false. If so, return null as view. Moodle
     * automatically does the redirecting.
     *
     * @param  Charon  $charon
     *
     * @return bool
     */
    private function checkPermissions(Charon $charon)
    {
        $course = $charon->courseModule()->moodleCourse;

        $permissions = $this->permissionsService->requireEnrollmentToCourse($course->id);
        if (!$permissions) {
            $this->permissionsService->redirectToEnrol($course->id);
            return false;
        }

        return true;
    }
}
