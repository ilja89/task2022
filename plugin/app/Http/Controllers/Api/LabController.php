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
     * LabDummyController constructor.
     *
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
                'chunk_size' => $this->request['chunk_size'],
                'own_teacher' => $this->request['own_teacher']
            ]),
            $course,
            $this->request['charons'],
            $this->request['teachers'],
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
     * Save lab.
     *
     * @version Registration 1.*
     *
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
     *
     * @version Registration 1.*
     *
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
     * @version Registration 1.*
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
     * TODO: This should not be in CourseController
     * @version Registration 1.*
     *
     * @param Course $course
     *
     * @return mixed
     */
    public function getCourse(Course $course)
    {
        return $this->labRepository->getCourse($course->id);
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
}
