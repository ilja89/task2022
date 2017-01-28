<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Services\PermissionsService;

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

    /** @var Request */
    protected $request;

    /**
     * AssignmentController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  Output $output
     * @param  Page $page
     * @param  PermissionsService $permissionsService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        Output $output,
        Page $page,
        PermissionsService $permissionsService
    ) {
        $this->request = $request;
        $this->charonRepository = $charonRepository;
        $this->output = $output;
        $this->page = $page;
        $this->permissionsService = $permissionsService;
    }

    /**
     * Render the assignment view where it shows one instance of the plugin.
     * This is the view shown to students.
     *
     * @return Factory|View
     */
    public function index()
    {
        $charon = $this->getCharon();
        $this->permissionsService->requireEnrollmentToCourse($charon->courseModule()->course);

        $this->addBreadcrumbs($charon);

        return view('assignment.index', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer(),
            'charon' => $charon,
        ]);
    }

    /**
     * Gets the Charon by the course module id. Wrapper for Charon repository
     * simpler to use.
     *
     * @return Charon
     */
    private function getCharon()
    {
        return $this->charonRepository->getCharonByCourseModuleIdEager($this->request['id']);
    }

    /**
     * Add breadcrumbs to the page.
     * Uses Moodle built in breadcrumbs.
     *
     * @param  Charon $charon
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
}
