<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabTeacher;
use Zeizig\Moodle\Services\ModuleService;
use function Sodium\add;

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
            $courseStartTimestamp = \DB::table('course')->where('id', $courseId)->select('startdate')->get()[0]->startdate;
            $courseStartDate = date('d-m-Y H:i:s', $courseStartTimestamp);
            $secondsInAMinute = 60;
            $secondsInAnHour = $secondsInAMinute * 60;
            $secondsInADay = $secondsInAnHour * 24;
            $secondsInAWeek = $secondsInADay * 7;
            $hoursToAddToStart = intval(Carbon::parse($start)->format('H')) - intval(Carbon::parse($courseStartDate)->format('H'));
            $hoursToAddToEnd = intval(Carbon::parse($end)->format('H')) - intval(Carbon::parse($courseStartDate)->format('H'));
            $minutesToAddToStart = intval(Carbon::parse($start)->format('i')) - intval(Carbon::parse($courseStartDate)->format('i'));
            $minutesToAddToEnd = intval(Carbon::parse($end)->format('i')) - intval(Carbon::parse($courseStartDate)->format('i'));
            $daysLabStart = intval(Carbon::parse($start)->format('w'));
            $daysCourseStart = intval(Carbon::parse($courseStartDate)->format('w'));
            $daysDiffToAdd = $daysLabStart - $daysCourseStart;
            foreach ($weeks as $week) {
                $thisLabStartDate = date('d-m-Y H:i:s', $courseStartTimestamp
                    + ($week - 1) * $secondsInAWeek +
                    $daysDiffToAdd * $secondsInADay +
                    $hoursToAddToStart * $secondsInAnHour +
                    $minutesToAddToStart * $secondsInAMinute);
                $thisLabEndDate = date('d-m-Y H:i:s', $courseStartTimestamp
                    + ($week - 1) * $secondsInAWeek +
                    $daysDiffToAdd * $secondsInADay +
                    $hoursToAddToEnd * $secondsInAnHour +
                    $minutesToAddToEnd * $secondsInAMinute);
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
        $labs = \DB::table('lab')
            ->where('course_id', $courseId)
            ->select('id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }

    public function getCourse($courseId) {
        $course = \DB::table('course')
            ->where('id', $courseId)
            ->select('*')
            ->get();
        return $course;
    }

}
