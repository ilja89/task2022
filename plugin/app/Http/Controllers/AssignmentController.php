<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Globals\User;

/**
 * Class AssignmentController.
 * Used to display the assignment view.
 *
 * @package TTU\Charon\Http\Controllers
 */
class AssignmentController extends Controller
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var Output */
    private $output;

    /** @var Page */
    private $page;

    /** @var Request */
    private $request;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var User */
    private $user;

    /**
     * AssignmentController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  Output $output
     * @param  Page $page
     * @param SubmissionsRepository $submissionsRepository
     * @param User $user
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        Output $output,
        Page $page,
        SubmissionsRepository $submissionsRepository,
        User $user
    ) {
        $this->request = $request;
        $this->charonRepository = $charonRepository;
        $this->output = $output;
        $this->page = $page;
        $this->submissionsRepository = $submissionsRepository;
        $this->user = $user;
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

        $this->initializePage($charon);

        $submissions = $this->submissionsRepository->getSubmissionsForStudentAndCharon($charon->id, $this->user->currentUserId());

        return view('assignment.index', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer(),
            'charon' => $charon,
            'submissions' => $submissions,
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
        $charon = $this->charonRepository->getCharonByCourseModuleIdEager($this->request['id']);
        return $charon;
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
        $this->page->addBreadcrumb($charon->name);
    }

    public function initializePage(Charon $charon)
    {
        $this->page->setUrl('/mod/charon/view.php', ['id' => $charon->courseModule()->id]);
        $this->page->setTitle($charon->name);
        $this->addBreadcrumbs($charon);
    }
}
