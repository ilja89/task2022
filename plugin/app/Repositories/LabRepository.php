<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
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
     * @return boolean
     */
    public function save($start, $end, $courseId, $teachers)
    {
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
        ->where('course_id', $courseId)
            ->select('id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }

}
