<?php

namespace TTU\Charon\Http\Middleware;

use Closure;
use TTU\Charon\Exceptions\CharonNotFoundException;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Services\PermissionsService;

class RequireEnrolment
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var PermissionsService */
    private $permissionsService;

    /**
     * @var User
     */
    private $user;

    /**
     * RequireEnrolment constructor.
     *
     * @param CharonRepository $charonRepository
     * @param PermissionsService $permissionsService
     * @param User $user
     */
    public function __construct(CharonRepository $charonRepository, PermissionsService $permissionsService, User $user)
    {
        $this->charonRepository = $charonRepository;
        $this->permissionsService = $permissionsService;
        $this->user = $user;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if ($request['id'] === null) {
                $charon = $this->charonRepository->getCharonByCourseModuleId($request->route('charon')->course);
            } else {
                $charon = $this->charonRepository->getCharonByCourseModuleId($request['id']);
            }
        } catch (CharonNotFoundException $e) {
            return redirect('/', 'The requested Charon could not be found.');
        }

        $modinfo = get_fast_modinfo($charon->course);
        $cm = $modinfo->get_cm($charon->courseModule()->id);
        try {
            // No redirect because VerifyCSRF needs to set some cookies
            require_login($charon->course, true, $cm, true, true);
        } catch (\require_login_exception $e) {
            if (!$cm->uservisible) {
                return redirect('/course/view.php?id=' . $charon->course, 'Sorry, this activity is currently hidden', null);
            } else {
                $this->permissionsService->requireEnrollmentToCourse($charon->courseModule()->course);
            }
        }

        return $next($request);
    }
}