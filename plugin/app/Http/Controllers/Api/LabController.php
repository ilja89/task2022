<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Models\Course;

class LabController extends Controller
{
    /** @var LabRepository */
    private $labRepository;

    /** @var LabService */
    private $labService;

    /**
     * @param Request $request
     * @param LabRepository $labRepository
     * @param LabService $labService
     */
    public function __construct(Request $request, LabRepository $labRepository, LabService $labService)
    {
        parent::__construct($request);
        $this->labRepository = $labRepository;
        $this->labService = $labService;
    }

    /**
     * Create a lab, optionally repeating.
     *
     * @version Registration 2.*
     *
     * @param Course $course
     *
     * @return int[]
     * @throws ValidationException
     */
    public function create(Course $course): array
    {
        $validator = Validator::make($this->request->all(), [
            'teachers' => 'required|filled',
            'charons' => 'required|filled',
            'start' => 'required|date|after:' . Carbon::now(),
            'end' => 'required|date|after:start',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->labService->create(
            new Lab([
                'course_id' => $course->id,
                'name' => $this->request['name'],
                'start' => Carbon::parse($this->request['start']),
                'end' => Carbon::parse($this->request['end']),
                'chunk_size' => $this->request['chunk_size']
            ]),
            $course,
            $this->request['charons'],
            $this->request['teachers'],
            $this->request['groups'] ? $this->request['groups'] : [],
            $this->request['weeks'] ? $this->request['weeks'] : []
        );
    }

    /**
     * Get Labs by course.
     *
     * @version Registration 1.*
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
     * @version Registration 1.*
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lab[]
     */
    public function all()
    {
        return $this->labRepository->getAllLabs();
    }

    /**
     * Update lab.
     *
     * @version Registration 2.*
     *
     * @param Course $course
     * @param Lab $lab
     *
     * @return Lab
     * @throws ValidationException
     */
    public function update(Course $course, Lab $lab)
    {
        if (Carbon::parse($lab->end)->isPast() && $this->labRepository->countRegistrations($lab->id) > 0) {
            throw ValidationException::withMessages(["It is not possible to edit lab in past. You need to create a new one."]);
        }

        $validator = Validator::make($this->request->all(), [
            'teachers' => 'required|filled',
            'charons' => 'required|filled',
            'start' => 'required|date|after:' . Carbon::now(),
            'end' => 'required|date|after:start',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $lab->name = $this->request['name'];
        $lab->start = Carbon::parse($this->request['start'])->format('Y-m-d H:i:s');
        $lab->end = Carbon::parse($this->request['end'])->format('Y-m-d H:i:s');
        $lab->chunk_size = $this->request['chunk_size'];

        return $this->labService->update(
            $lab,
            $course,
            $this->request['charons'],
            $this->request['teachers'],
            $this->request['groups'] ? $this->request['groups'] : []
        );
    }

    /**
     * Delete lab.
     *
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
     * @version Registration 1.*
     *
     * @param Charon $charon
     *
     * @return Lab[]|Collection
     */
    public function getByCharon(Charon $charon)
    {
        return $this->labRepository->getLabsByCharonId($charon->id);
    }

    /**
     * Gets all groups and groupings for course
     *
     * @param int $courseId The course identifier
     *
     * @return array Array containing arrays of groups and groupings
     */
    public function getGroups(int $courseId): array
    {
        $groups = $this->labRepository->getAllGroups($courseId);
        $groupings = $this->labRepository->getAllGroupings($courseId);

        // collect info about groups together, into single grouping object
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
     * @version Registration 1.*
     *
     * @param Request $request
     *
     * @return Lab[]|Collection
     */
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

    /**
     * @param Course $course (not used)
     * @param Lab $lab
     *
     * @return int
     */
    public function countRegistrations(Course $course, Lab $lab): int
    {
        $start = $this->request['start'] ? Carbon::parse($this->request['start'])->format('Y-m-d H:i:s') : null;
        $end = $this->request['end'] ? Carbon::parse($this->request['end'])->format('Y-m-d H:i:s') : null;
        $charons = $this->request['charons'] ?? null;
        $teachers = $this->request['teachers'] ?? null;
        return $this->labRepository->countRegistrations($lab->id, $start, $end, $charons, $teachers);
    }
}
