<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Services\SubmissionCalculatorService;
use Zeizig\Moodle\Globals\Output;
use Zeizig\Moodle\Globals\Page;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\PermissionsService;

/**
 * Class AssignmentController.
 * Used to display the assignment view.
 *
 * @package TTU\Charon\Http\Controllers
 */
class AssignmentController extends Controller
{
    /** @var GradebookService */
    protected $gradebookService;

    /** @var SubmissionCalculatorService */
    protected $submissionCalculatorService;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var Output */
    private $output;

    /** @var Page */
    private $page;

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
     * @param User $user
     * @param PermissionsService $permissionsService
     * @param GradebookService $gradebookService
     * @param SubmissionCalculatorService $submissionCalculatorService
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        Output $output,
        Page $page,
        User $user,
        PermissionsService $permissionsService,
        GradebookService $gradebookService,
        SubmissionCalculatorService $submissionCalculatorService
    ) {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
        $this->output = $output;
        $this->page = $page;
        $this->user = $user;
        $this->permissionsService = $permissionsService;
        $this->gradebookService = $gradebookService;
        $this->submissionCalculatorService = $submissionCalculatorService;
    }

    /**
     * Render the assignment view where it shows one instance of the plugin.
     * This is the view shown to students.
     *
     * @return Factory|View
     *
     * @throws \TTU\Charon\Exceptions\CharonNotFoundException
     */
    public function index()
    {
        $charon = $this->getCharon($this->request->input('id'));

        $this->initializePage($charon);

        return view('assignment.index', [
            'header' => $this->output->header(),
            'footer' => $this->output->footer(),
            'charon' => $charon,
            'course_module_id' => $this->request->input('id'),
            'can_edit' => $this->permissionsService->canManageCourse($charon->course),
            'student_id' => $this->user->currentUserId(),
        ]);
    }

    /**
     * Gets the Charon by the course module id. Wrapper for Charon repository
     * simpler to use.
     *
     * @param $courseModuleId
     * @return Charon
     * @throws \TTU\Charon\Exceptions\CharonNotFoundException
     */
    private function getCharon($courseModuleId)
    {
        $charon = $this->charonRepository->getCharonByCourseModuleIdEager($courseModuleId);
        $charon->maxGrade = $charon->category->getGradeItem()->grademax;
        $charon->userGrade = $this->submissionCalculatorService->getUserActiveGradeForCharon(
            $charon, $this->user->currentUserId()
        );

        foreach ($charon->grademaps as $grademap) {
            $grademap->userGrade = $this->gradebookService->getGradeForGradeItemAndUser(
                $grademap->grade_item_id, $this->user->currentUserId()
            );
        }

        return $charon;
    }

    public function initializePage(Charon $charon)
    {
        $this->page->setUrl('/mod/charon/view.php', ['id' => $charon->courseModule()->id]);
        $this->page->setTitle($charon->name);
    }
}
