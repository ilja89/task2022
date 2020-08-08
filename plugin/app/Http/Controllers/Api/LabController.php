<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
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
     * @param  Course $course
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
    public function all() {
        return $this->labRepository->getAllLabs();
    }

    /**
     * Save lab.
     * @param Course $course
     *
     * @return bool
     */
    public function save(Course $course) {
        return $this->labRepository->save($this->request['start'], $this->request['end'], $course->id, $this->request['teachers'], $this->request['weeks']);
    }

    /**
     * Delete lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function delete(Course $course, Lab $lab) {
        return $this->labRepository->deleteByInstanceId($lab->id);
    }

    /**
     * Update lab.
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     */
    public function update(Course $course, Lab $lab) {
        return $this->labRepository->update($lab->id, $this->request['start'], $this->request['end'], $this->request['teachers']);
    }

    /**
     * @param Course $course
     * @return mixed
     */
    public function getCourse(Course $course) {
        return $this->labRepository->getCourse($course->id);
    }

}
