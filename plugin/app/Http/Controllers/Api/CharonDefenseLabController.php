<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\LabDummy;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\LabDummyRepository;
use Zeizig\Moodle\Models\Course;

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

    /**
     * Get Charons by course.
     *
     * @param  Charon $charon
     *
     * @return \Illuminate\Database\Eloquent\Collection|CharonDefenseLab[]
     */
    public function getByCharon(Charon $charon)
    {
        //return $this->charonDefenseLabRepository->findLabsByCourse($charon->id);
        return $this->charonDefenseLabRepository->getDefenseLabsByCharonId($charon->id);
    }
}
