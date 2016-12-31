<?php

namespace TTU\Charon\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TTU\Charon\Repositories\CharonRepository;

/**
 * Class InstanceFormController.
 * Renders the instance form when updating or editing the plugin.
 *
 * @package TTU\Charon\Http\Controllers
 */
class InstanceFormController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /**
     * InstanceFormController constructor.
     *
     * @param  CharonRepository  $charonRepository
     */
    public function __construct(CharonRepository $charonRepository)
    {
        $this->charonRepository = $charonRepository;
    }

    /**
     * Renders the instance form when creating a new instance.
     *
     * @param  Request  $request
     *
     * @return Factory|View
     */
    public function index(Request $request) {

        if (isset($request->update)) {
            $charon = $this->charonRepository->getCharonByCourseModuleId($request->update);

            return view('instanceForm.form', compact('charon'));
        }

        return view('instanceForm.form');
    }
}
