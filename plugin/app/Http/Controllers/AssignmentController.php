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
    private $charonRepository;

    /** @var Output */
    private $output;

    /** @var Page */
    private $page;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var User */
    private $user;

    /** @var PermissionsService */
    private $permissionsService;

    /**
     * AssignmentController constructor.
     *
     * @param  Request $request
     * @param  CharonRepository $charonRepository
     * @param  Output $output
     * @param  Page $page
     * @param SubmissionsRepository $submissionsRepository
     * @param User $user
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        Output $output,
        Page $page,
        SubmissionsRepository $submissionsRepository,
        User $user,
        PermissionsService $permissionsService
    ) {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
        $this->output = $output;
        $this->page = $page;
        $this->submissionsRepository = $submissionsRepository;
        $this->user = $user;
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

        $this->initializePage($charon);

        return view('assignment.index', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer(),
            'charon' => $charon,
            'can_edit' => $this->permissionsService->canManageCourse($charon->course),
            'student_id' => $this->user->currentUserId(),
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

    public function initializePage(Charon $charon)
    {
        $this->page->setUrl('/mod/charon/view.php', ['id' => $charon->courseModule()->id]);
        $this->page->setTitle($charon->name);
    }
}
