<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CharonRepository;
use Zeizig\Moodle\Models\Course;

/**
 * Class PopupController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PopupController extends Controller
{
    /** @var CharonRepository */
    protected $charonRepository;

    /**
     * PopupController constructor.
     *
     * @param CharonRepository $charonRepository
     */
    public function __construct(CharonRepository $charonRepository)
    {
        $this->charonRepository = $charonRepository;
    }

    /**
     * @param  Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Charon[]
     */
    public function getCharonsByCourse(Course $course)
    {
        return $this->charonRepository->findCharonsByCourse($course->id);
    }
}
