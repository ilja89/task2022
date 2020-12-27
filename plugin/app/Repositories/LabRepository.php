<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
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
     * Save the lab instance.
     *
     * @param $start
     * @param $end
     * @param $courseId
     * @param $teachers
     *
     * @param $weeks
     * @return boolean
     */
    public function save($start, $end, $courseId, $teachers, $charons, $weeks)
    {
        $allCarbonStartDatesForLabs = array();

        $labStartCarbon = Carbon::parse($start, 'UTC');
        $labEndCarbon = Carbon::parse($end, 'UTC');

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
     * @return \Illuminate\Database\Eloquent\Collection|static[]
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
     * @param integer $id
     *
     * @return Lab
     *
     * @throws \Exception
     */
    public function deleteByInstanceId($id)
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
     * @param Number $oldLabId
     * @param Carbon $newStart
     * @param Carbon $newEnd
     * @param $newTeachers
     * @param $newCharons
     *
     * @return Lab
     */
    public function update($oldLabId, $newStart, $newEnd, $newTeachers, $newCharons)
    {
        $newStartCarbon = Carbon::parse($newStart, 'UTC');
        $newEndCarbon = Carbon::parse($newEnd, 'UTC');

        $oldLab = Lab::find($oldLabId);

        $this->validateLab($newTeachers, $oldLab->course_id, $newStartCarbon, $newEndCarbon);

        $oldLab->start = $newStartCarbon->format('Y-m-d H:i:s');
        $oldLab->end = $newEndCarbon->format('Y-m-d H:i:s');

        $oldLabTeachers = $this->labTeacherRepository->getTeachersByLabId($oldLab->course_id, $oldLabId);
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
     * @param integer $courseId
     *
     * @return Lab[]
     */
    public function findLabsByCourse($courseId)
    {
        $labs = \DB::table('charon_lab')
            ->where('course_id', $courseId)
            ->select('id', 'start', 'end', 'course_id')
            ->orderBy('start')
            ->get();

        for ($i = 0; $i < count($labs); $i++) {
            $labs[$i]->teachers = $this->labTeacherRepository->getTeachersByLabId($courseId, $labs[$i]->id);
        }

        for ($i = 0; $i < count($labs); $i++) {
            $labs[$i]->charons = $this->getCharonsForLab($courseId, $labs[$i]->id);
        }

        return $labs;
    }

    public function getCourse($courseId)
    {
        $course = \DB::table('course')
            ->where('id', $courseId)
            ->select('*')
            ->get();
        return $course;
    }

    /**
     * @param $courseId
     * @param $labId
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
     * @param $charonId
     * @return Lab[]
     */
    public function getLabsByCharonId($charonId)
    {
        return \DB::table('charon_lab')
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_id', $charonId)
            ->select('charon_defense_lab.id', 'start', 'end', 'course_id')
            ->get();
    }

    /**
     * @param $charonId
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
     * @param $charonId
     */
    public function deleteLab($charonId, $labId)
    {
        return \DB::table('charon_defense_lab')
            ->where('charon_id', $charonId)
            ->where('lab_id', $labId)
            ->delete();
    }

    /**
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
     * @param $teachers
     * @param $courseId
     * @return void
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
