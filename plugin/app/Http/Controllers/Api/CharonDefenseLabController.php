<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\LabDummy;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\LabDummyRepository;

class CharonDefenseLabController extends Controller
{
    /** @var CharonDefenseLabRepository */
    private $charonDefenseLabRepository;

    /**
     * CharonDefenseLabController constructor.
     *
     * @param Request $request
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     */
    public function __construct(Request $request, CharonDefenseLabRepository $charonDefenseLabRepository)
    {
        parent::__construct($request);
        $this->charonDefenseLabRepository = $charonDefenseLabRepository;
    }
}
