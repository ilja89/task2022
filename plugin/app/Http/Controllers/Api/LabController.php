<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use Zeizig\Moodle\Models\Course;

class LabController extends Controller
{
    /** @var LabRepository */
    private $labRepository;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param LabRepository $labRepository
     */
    public function __construct(Request $request, LabRepository $labRepository)
    {
        parent::__construct($request);
        $this->labRepository = $labRepository;
    }

    /**
     * Get Labs by course.
     *
     * @param Course $course
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lab[]
     */
    public function getByCourse(Course $course)
    {
        return $this->labRepository->findLabsByCourse($course->id);
    }

    /**
     * Get all Labs.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lab[]
     */
    public function all()
    {
        return $this->labRepository->getAllLabs();
    }

    /**
     * Save lab.
     * @param Course $course
     *
     * @return bool
     */
    public function save(Course $course)
    {
        return $this->labRepository->save(
            $this->request['start'],
            $this->request['end'],
            $this->request['name'],
            $course->id,
            $this->request['teachers'],
            $this->request['charons'],
            $this->request['weeks']
        );
    }

    /**
     * Update lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function update(Course $course, Lab $lab)
    {
        return $this->labRepository->update(
            $lab->id,
            $this->request['start'],
            $this->request['end'],
            $this->request['name'],
            $this->request['teachers'],
            $this->request['charons']
        );
    }

    /**
     * Delete lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function delete(Course $course, Lab $lab)
    {
        return $this->labRepository->deleteByInstanceId($lab->id);
    }

    /**
     * @param Course $course
     * @return mixed
     */
    public function getCourse(Course $course)
    {
        return $this->labRepository->getCourse($course->id);
    }

    /**
     * @param Charon $charon
     * @return Lab[]
     */
    public function getByCharon(Charon $charon)
    {
        return $this->labRepository->getLabsByCharonId($charon->id);
    }

    public function findLabsByCharonLaterEqualToday(Request $request)
    {
        $charonId = $request->route('charon');
        return \DB::table('charon_lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->where('end', '>=', Carbon::now())
            ->select('charon_defense_lab.id', 'start', 'end', 'name', 'course_id')
            ->get();
    }

    public function registrations(Course $course, Lab $lab)
    {
        return $this->labRepository->getRegistrations($lab, $this->request);
    }

}
