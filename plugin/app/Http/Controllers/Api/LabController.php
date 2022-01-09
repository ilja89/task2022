<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

class LabController extends Controller
{
    /** @var LabRepository */
    private $labRepository;

    /** @var LabService */
    private $labService;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param LabRepository $labRepository
     * @param LabService $labService
     */
    public function __construct(
        Request $request,
        LabRepository $labRepository,
        LabService $labService
    ) {
        parent::__construct($request);
        $this->labRepository = $labRepository;
        $this->labService = $labService;
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
            $this->request['groups'],
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
        return $this->labService->update($this->request, $lab);
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
     * Gets all groups and groupings for course
     *
     * @param int $courseId         The course identifier
     * @return []                   Array containing arrays of groups and groupings
     */
    public function getGroups(int $courseId)
    {
        $groups = $this->labRepository->getAllGroups($courseId);
        $groupings = $this->labRepository->getAllGroupings($courseId);

        //collect info about groups together, into single grouping object
        $groupObjects = array_column($groups->toArray(), null, "id");
        $result = [];
        foreach ($groupings as $g)
        {
            $id = $g['id'];
            $groupid = $g['groupid'];
            $group = $groupObjects[$groupid];
            if (array_key_exists($id, $result)) {
                array_push($result[$id]['groups'], $group);
            } else {
                $result[$id] = array(
                    'id' => $id,
                    'name' => $g['name'],
                    'groups' => array($group)
                );
            }
        }

        $result = array_column($result, null);
        return ['groups' => $groups, 'groupings' => $result];
    }

    /**
     * @param Charon $charon
     *
     * @return mixed
     */
    public function findAvailableLabsByCharon(Charon $charon)
    {
        return $this->labService->findAvailableLabsByCharon($charon->id);
    }

    /**
     * @param Course $course (not used)
     * @param Lab $lab
     *
     * @return int
     */
    public function countRegistrations(Course $course, Lab $lab)
    {
        $start = $this->request['start'] ? Carbon::parse($this->request['start'])->format('Y-m-d H:i:s') : null;
        $end = $this->request['end'] ? Carbon::parse($this->request['end'])->format('Y-m-d H:i:s') : null;
        $charons = $this->request['charons'] ?? null;
        $teachers = $this->request['teachers'] ?? null;
        return $this->labRepository->countRegistrations($lab->id, $start, $end, $charons, $teachers);
    }

    /**
     * Returns queue status in the form of an array, with approximate defence times.
     *
     * @param Charon $charon
     * @param CharonDefenseLab $defenseLab
     * @return array
     */
    public function getLabQueueStatus(Charon $charon, CharonDefenseLab $defenseLab)
    {
        return $this->labService->labQueueStatus(app(User::class)->currentUser(), $defenseLab->lab);
    }
}
