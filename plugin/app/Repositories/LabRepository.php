<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabTeacher;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Class CharonRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class LabRepository
{
    /** @var ModuleService */
    protected $moduleService;
    /** @var LabTeacherRepository */
    protected $labTeacherRepository;
    /** @var CharonDefenseLabRepository */
    protected $charonDefenseLabRepository;

    /**
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param LabTeacherRepository $labTeacherRepository
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     */
    public function __construct(
        ModuleService $moduleService,
        LabTeacherRepository $labTeacherRepository,
        CharonDefenseLabRepository $charonDefenseLabRepository
    ) {
        $this->moduleService = $moduleService;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->charonDefenseLabRepository = $charonDefenseLabRepository;
    }

    /**
     * @version Registration 2.*
     *
     * @param array $collection
     */
    public function createManyLabCharons(array $collection)
    {
        CharonDefenseLab::insert($collection);
    }

    /**
     * Save the lab instance.
     *
     * @version Registration 1.*
     *
     * @param $start
     * @param $end
     * @param $name
     * @param $courseId
     * @param $teachers
     * @param $charons
     * @param $weeks
     *
     * @return boolean
     */
    public function save($start, $end, $name, $courseId, $teachers, $charons, $weeks)
    {
        $allCarbonStartDatesForLabs = array();

        $labStartCarbon = Carbon::parse($start);
        $labEndCarbon = Carbon::parse($end);

        $this->validateLab($teachers, $courseId, $labStartCarbon, $labEndCarbon);

        $labLength = CarbonInterval::hours($labStartCarbon->diffInHours($labEndCarbon))
            ->minutes($labStartCarbon->diffInMinutes($labEndCarbon) % 60);

        if ($weeks) {
            $courseStartTimestamp = \DB::table('course')->where('id', $courseId)->select('startdate')->get()[0]->startdate;
            $courseStartCarbon = Carbon::createFromTimestamp($courseStartTimestamp)->startOfWeek();

            $firstWeekLabStart = $courseStartCarbon->copy()->addDays($courseStartCarbon->diffInDays($labStartCarbon) % 7);
            $firstWeekLabStart->hour = $labStartCarbon->hour;
            $firstWeekLabStart->minute = $labStartCarbon->minute;

            foreach ($weeks as $week) {
                $allCarbonStartDatesForLabs[] = $firstWeekLabStart->copy()->addWeeks($week - 1);
            }
        }

        if (!in_array($labStartCarbon, $allCarbonStartDatesForLabs)) {
            $allCarbonStartDatesForLabs[] = $labStartCarbon;
        }

        foreach ($allCarbonStartDatesForLabs as $labStartDate) {
            $lab = Lab::create([
                'start' => $labStartDate->format('Y-m-d H:i:s'),
                'end' => $labStartDate->copy()->add($labLength)->format('Y-m-d H:i:s'),
                'name' => $name,
                'course_id' => $courseId
            ]);

            foreach ($teachers as $teacher) {
                LabTeacher::create([
                    'lab_id' => $lab->id,
                    'teacher_id' => $teacher
                ])->save();
            }

            foreach ($charons as $charon) {
                CharonDefenseLab::create([
                    'lab_id' => $lab->id,
                    'charon_id' => $charon
                ])->save();
            }

            $lab->save();
        }

        return $lab;
    }

    /**
     * Get all labs.
     *
     * @return Collection|static[]
     */
    public function getAllLabs()
    {
        return Lab::all();
    }

    /**
     * Get an instance of Charon by its id.
     *
     * @param integer $id
     *
     * @return Lab
     */
    public function getLabById($id)
    {
        return Lab::find($id);
    }

    /**
     * Deletes the instance with given id.
     *
     * @version Registration 1.*
     *
     * @param integer $id
     *
     * @return Lab
     * @throws Exception
     */
    public function deleteByInstanceId($id): Lab
    {
        /** @var Lab $lab */
        $lab = Lab::find($id);

        CharonDefenseLab::where('lab_id', $id)->delete();
        LabTeacher::where('lab_id', $id)->delete();

        $lab->delete();
        return $lab;
    }

    /**
     * Takes the old instance and override its values with the new Charon values.
     *
     * @version Registration 1.*
     *
     * @param Number $oldLabId
     * @param Carbon $newStart
     * @param Carbon $newEnd
     * @param $newTeachers
     * @param $newCharons
     *
     * @return Lab
     */
    public function update($oldLabId, $newStart, $newEnd, $name, $newTeachers, $newCharons)
    {
        $newStartCarbon = Carbon::parse($newStart);
        $newEndCarbon = Carbon::parse($newEnd);
        $oldLab = Lab::find($oldLabId);

        $this->validateLab($newTeachers, $oldLab->course_id, $newStartCarbon, $newEndCarbon);

        $oldLab->name = $name;
        $oldLab->start = $newStartCarbon->format('Y-m-d H:i:s');
        $oldLab->end = $newEndCarbon->format('Y-m-d H:i:s');

        $oldLabTeachers = $this->labTeacherRepository->getTeachersByLabAndCourse($oldLab->course_id, $oldLabId);
        $oldLabCharons = $this->getCharonsForLab($oldLab->course_id, $oldLabId);

        foreach ($oldLabTeachers as $oldLabTeacher) {
            if (!in_array($oldLabTeacher->id, $newTeachers)) {
                $this->labTeacherRepository->deleteByLabAndTeacherId($oldLabId, $oldLabTeacher->id);
            }
        }

        $oldLabTeacherIds = array_map(
            function ($a) {
                return $a->id;
            },
            $oldLabTeachers->toArray()
        );

        foreach ($newTeachers as $newTeacherId) {
            if (!in_array($newTeacherId, $oldLabTeacherIds)) {
                LabTeacher::create([
                    'lab_id' => $oldLab->id,
                    'teacher_id' => $newTeacherId
                ])->save();
            }
        }

        foreach ($oldLabCharons as $oldLabCharon) {
            if (!in_array($oldLabCharon->id, $newCharons)) {
                $this->charonDefenseLabRepository->deleteDefenseLabByLabAndCharon($oldLabId, $oldLabCharon->id);
            }
        }

        $oldLabCharonIds = array_map(
            function ($a) {
                return $a->id;
            },
            $oldLabCharons->toArray()
        );

        foreach ($newCharons as $newCharonId) {
            if (!in_array($newCharonId, $oldLabCharonIds)) {
                CharonDefenseLab::create([
                    'lab_id' => $oldLab->id,
                    'charon_id' => $newCharonId
                ])->save();
            }
        }

        $oldLab->save();
        return $oldLab;
    }

    /**
     * Find all labs in course with given id.
     *
     * @version Registration 1.*
     *
     * @param integer $courseId
     *
     * @return Lab[]
     */
    public function findLabsByCourse($courseId)
    {
        $labs = \DB::table('charon_lab')
            ->where('course_id', $courseId)
            ->select('id', 'start', 'end', 'name', 'course_id')
            ->orderBy('start')
            ->get();

        for ($i = 0; $i < count($labs); $i++) {
            $labs[$i]->teachers = $this->labTeacherRepository->getTeachersByLabAndCourse($courseId, $labs[$i]->id);
        }

        for ($i = 0; $i < count($labs); $i++) {
            $labs[$i]->charons = $this->getCharonsForLab($courseId, $labs[$i]->id);
        }

        return $labs;
    }

    /**
     * TODO: This should be in CourseRepository
     */
    public function getCourse($courseId)
    {
        $course = \DB::table('course')
            ->where('id', $courseId)
            ->select('*')
            ->get();
        return $course;
    }

    /**
     * @version Registration 1.*
     *
     * @param $courseId
     * @param $labId
     *
     * @return Object[]
     */
    public function getCharonsForLab($courseId, $labId)
    {
        return \DB::table('charon_lab')
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('course_id', $courseId)
            ->where('lab_id', $labId)
            ->join('charon', 'charon_defense_lab.charon_id', 'charon.id')
            ->select('charon.id', 'charon.project_folder')
            ->get();
    }

    /**
     * @version Registration 1.*
     *
     * @param $charonId
     *
     * @return Lab[]
     */
    public function getLabsByCharonId($charonId)
    {
        return \DB::table('charon_lab')
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_id', $charonId)
            ->select('charon_defense_lab.id', 'start', 'end', 'name', 'course_id')
            ->get();
    }

    /**
     * @version Registration 1.*
     *
     * @param $charonId
     *
     * @return int[]
     */
    public function getLabsIdsByCharonId($charonId)
    {
        return \DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->pluck('lab_id')
            ->toArray();
    }

    /**
     * @version Registration 1.*
     *
     * @param $charonId
     * @param $labId
     *
     * @return mixed
     */
    public function deleteLab($charonId, $labId)
    {
        return \DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->where('lab_id', $labId)
            ->delete();
    }

    /**
     * @version Registration 1.*
     *
     * @param $charonId
     * @param $labId
     */
    public function makeLab($charonId, $labId)
    {
        CharonDefenseLab::create([
            'lab_id' => $labId,
            'charon_id' => $charonId
        ]);
    }

    /**
     * Validate lab times and teachers. Throw http exceptions when validation not passed.
     *
     * @version Registration 1.*
     *
     * @param $teachers
     * @param $courseId
     */
    private function validateLab($teachers, $courseId, $carbonStart, $carbonEnd)
    {
        $courseTeacherIds = array_map(
            function ($a) {
                return $a->id;
            },
            $this->labTeacherRepository->getTeachersByCourseId($courseId)->toArray()
        );

        foreach ($teachers as $teacher) {
            if (!in_array($teacher, $courseTeacherIds)) {
                throw new BadRequestHttpException("Lab teachers have to be teachers in the course.");
            }
        }

        if ($carbonEnd->lessThanOrEqualTo($carbonStart)) {
            throw new BadRequestHttpException("Lab end has to be after lab start.");
        }

        $labLength = CarbonInterval::hours($carbonStart->diffInHours($carbonEnd))
            ->minutes($carbonStart->diffInMinutes($carbonEnd) % 60);

        if ($labLength->hours >= 24) {
            throw new BadRequestHttpException("Lab has to be below 24 hours long.");
        }
    }

}
