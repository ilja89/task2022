<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
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
    /**
     * @var LabTeacherRepository
     */
    protected $labTeacherRepository;

    /**
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(ModuleService $moduleService, LabTeacherRepository $labTeacherRepository)
    {
        $this->moduleService = $moduleService;
        $this->labTeacherRepository = $labTeacherRepository;
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
    public function save($start, $end, $courseId, $teachers, $weeks) {
        $allStartDatesForLabs = array();
        if ($weeks) {
            $labStartCarbon = Carbon::parse($start);
            $labEndCarbon = Carbon::parse($end);

            // hard limits the lab length to 23h 59 mins
            $labLength = CarbonInterval::hours($labStartCarbon->diffInHours($labEndCarbon) % 24)
                ->minutes($labStartCarbon->diffInMinutes($labEndCarbon) % 60);

            $courseStartTimestamp = \DB::table('course')->where('id', $courseId)->select('startdate')->get()[0]->startdate;
            $courseStartCarbon = Carbon::createFromTimestamp($courseStartTimestamp)->startOfWeek();

            $firstWeekLabStart = $courseStartCarbon->copy()->addDays($courseStartCarbon->diffInDays($labStartCarbon) % 7);
            $firstWeekLabStart->hour = $labStartCarbon->hour;
            $firstWeekLabStart->minute = $labStartCarbon->minute;

            foreach ($weeks as $week) {
                $thisLabCarbonStart = $firstWeekLabStart->copy()->addWeeks($week - 1);
                $thisLabCarbonEnd = $thisLabCarbonStart->copy()->add($labLength);

                $thisLabStartDate = date('d-m-Y H:i:s', $thisLabCarbonStart->timestamp);
                $thisLabEndDate = date('d-m-Y H:i:s', $thisLabCarbonEnd->timestamp);

                $lab = Lab::create([
                    'start' => Carbon::parse($thisLabStartDate)->format('Y-m-d H:i:s'),
                    'end' => Carbon::parse($thisLabEndDate)->format('Y-m-d H:i:s'),
                    'course_id' => $courseId
                ]);
                $allStartDatesForLabs[] = $thisLabStartDate;
                for ($i = 0; $i < count($teachers); $i++) {
                    $labTeacher = LabTeacher::create([
                        'lab_id' => $lab->id,
                        'teacher_id' => $teachers[$i]
                    ]);
                    $labTeacher->save();
                }
                $lab->save();
            }
        }
        if (!in_array(Carbon::parse($start)->format('d-m-Y H:i:s'), $allStartDatesForLabs)) {
            $lab = Lab::create([
                'start'  => Carbon::parse($start)->format('Y-m-d H:i:s'),
                'end' => Carbon::parse($end)->format('Y-m-d H:i:s'),
                'course_id' => $courseId
            ]);
            for ($i = 0; $i < count($teachers); $i++) {
                $labTeacher = LabTeacher::create([
                    'lab_id' => $lab->id,
                    'teacher_id' => $teachers[$i]
                ]);
                $labTeacher->save();
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
     * @param  integer  $id
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
     * @param  integer $id
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
     *
     * @param $teachers
     * @return Lab
     */
    public function update($oldLabId, $newStart, $newEnd, $teachers)
    {
        $oldLab = Lab::find($oldLabId);
        $oldLab->start = Carbon::parse($newStart)->format('Y-m-d H:i:s');
        $oldLab->end = Carbon::parse($newEnd)->format('Y-m-d H:i:s');

        // delete prev lab teachers
        $this->labTeacherRepository->deleteByLabId($oldLab->id);
        for ($i = 0; $i < count($teachers); $i++) {
            $labTeacher = LabTeacher::create([
                'lab_id' => $oldLab->id,
                'teacher_id' => $teachers[$i]
            ]);
            $labTeacher->save();
        }
        $oldLab->save();
        return $oldLab;
    }

    /**
     * Find all labs in course with given id.
     *
     * @param  integer $courseId
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
        for($i = 0; $i < count($labs); $i++) {
            $labs[$i]->teachers = $this->labTeacherRepository->getTeachersByLabId($courseId, $labs[$i]->id);
        }
        return $labs;
    }

    public function getCourse($courseId) {
        $course = \DB::table('course')
            ->where('id', $courseId)
            ->select('*')
            ->get();
        return $course;
    }

    /**
     * @param $charonId
     * @return Lab[]
     */
    public function getLabsByCharonId($charonId) {
        $labs = \DB::table('charon_lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->select('charon_defense_lab.id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }
}
