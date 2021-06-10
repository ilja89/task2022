<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\LabTeacher;
use TTU\Charon\Models\LabGroup;
use Zeizig\Moodle\Services\ModuleService;
use Zeizig\Moodle\Models\Grouping;
use Zeizig\Moodle\Models\Group;

/**
 * Class CharonRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class LabRepository
{
    /** @var string */
    private $prefix;

    /** @var ModuleService */
    private $moduleService;

    /** @var LabTeacherRepository */
    private $labTeacherRepository;

    /** @var CharonDefenseLabRepository */
    private $charonDefenseLabRepository;

    /**
     * LabRepository constructor.
     *
     * @param MoodleConfig $moodleConfig
     * @param ModuleService $moduleService
     * @param LabTeacherRepository $labTeacherRepository
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     */
    public function __construct(
        MoodleConfig $moodleConfig,
        ModuleService $moduleService,
        LabTeacherRepository $labTeacherRepository,
        CharonDefenseLabRepository $charonDefenseLabRepository
    ) {
        $this->prefix = $moodleConfig->prefix;
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
     * @version Registration 2.*
     *
     * @param array $charonIds
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return Collection|Lab[] Returned labs have an extra field `charons` for holding their ID-s
     */
    public function findLabsForCharons(array $charonIds, Carbon $start, Carbon $end): Collection
    {
        $labs = DB::table('charon_lab')
            ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->whereDate('charon_lab.start', '>=', $start->format('Y-m-d'))
            ->whereDate('charon_lab.end', '<=', $end->format('Y-m-d'))
            ->whereRaw('IF(date(start) = ?, time(start)>=time(?), true)', [$start->format('Y-m-d'), $start->toTimeString()])
            ->whereRaw('IF(date(end) = ?, time(end)>=time(?), true)', [$end->format('Y-m-d'), $end->toTimeString()])
            ->whereIn('charon_defense_lab.charon_id', $charonIds)
            ->groupBy('charon_lab.id', 'charon_lab.start', 'charon_lab.end', 'charon_lab.course_id', 'charon_lab.name', 'charon_lab.chunk_size')
            ->select(DB::raw($this->prefix . 'charon_lab.*, GROUP_CONCAT(' . $this->prefix . 'charon_defense_lab.charon_id) AS charons'))
            ->get()
            ->map(function ($lab) {
                $lab->charons = array_map('intval', explode(',', $lab->charons));
                return $lab;
            })
            ->toArray();

        return Lab::hydrate($labs);
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
     * @param $groups
     * @param $weeks
     *
     * @return boolean
     */
    public function save($start, $end, $name, $courseId, $teachers, $charons, $groups, $weeks)
    {
        $allCarbonStartDatesForLabs = array();

        $labStartCarbon = Carbon::parse($start);
        $labEndCarbon = Carbon::parse($end);

        $this->validateLab($teachers, $courseId, $labStartCarbon, $labEndCarbon);

        $labLength = CarbonInterval::hours($labStartCarbon->diffInHours($labEndCarbon))
            ->minutes($labStartCarbon->diffInMinutes($labEndCarbon) % 60);

        if ($weeks) {
            $courseStartTimestamp = DB::table('course')->where('id', $courseId)->select('startdate')->get()[0]->startdate;
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

            foreach ($groups as $group) {
                LabGroup::create([
                    'lab_id' => $lab->id,
                    'group_id' => $group
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
     * @param $newGroups
     *
     * @return Lab
     */
    public function update($oldLabId, $newStart, $newEnd, $name, $newTeachers, $newCharons, $newGroups)
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

        $oldGroups = $this->getGroupsForLab($oldLab->course_id, $oldLabId)->pluck('id')->toArray();
        $toBeRemoved = array_diff($oldGroups, $newGroups);
        $toBeAdded = array_diff($newGroups, $oldGroups);

        foreach ($toBeRemoved as $id) {
            $this->deleteGroupForLab($oldLabId, $id);
        }

        foreach ($toBeAdded as $id) {
            LabGroup::create([
                'lab_id' => $oldLabId,
                'group_id' => $id
            ])->save();
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
        $labs = DB::table('charon_lab')
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

        for ($i = 0; $i < count($labs); $i++) {
            $labs[$i]->groups = $this->getGroupsForLab($courseId, $labs[$i]->id);
        }

        return $labs;
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
        return DB::table('charon_lab')
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
        return DB::table('charon_lab')
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
        return DB::table('charon_defense_lab')
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
        return DB::table('charon_defense_lab')
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
     * Count affected registrations for lab
     * request body may contain additional filters
     * 
     * @version Registration 1.*
     * 
     * @param int $labId
     * @param string $start
     * @param string $end
     * @param int[] $charons
     * @param int[] $teachers
     * @return int
     * 
     */
    public function countRegistrations($labId, $start, $end, $charons, $teachers)
    {

        return \DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->where('charon_defense_lab.lab_id', $labId)
            ->where('charon_defenders.progress', '<>', 'Done')
            ->where(function($q) use($start, $end, $charons, $teachers) {
                if ($start) {
                    $q = $q->orWhere('charon_defenders.choosen_time', '<', $start);
                }
                if ($end) {
                    $q = $q->orWhere('charon_defenders.choosen_time', '>', $end);
                }
                if ($charons) {
                    $q = $q->orWhereIn('charon_defenders.charon_id', $charons);
                }
                if ($teachers) {
                    $q = $q->orWhereIn('charon_defenders.teacher_id', $teachers);
                }
            })->count();
    }

    /**
     * Gets the groups array for lab.
     *
     * @param int $courseId     The course identifier
     * @param int $labId        The lab identifier
     * @return []               Groups for lab.
     */
    public function getGroupsForLab($courseId, $labId)
    {
        return LabGroup::where('lab_id', $labId)
            ->join('groups', 'groups.id', 'charon_lab_group.group_id')
            ->select('groups.id', 'groups.name')
            ->get();
    }

    /**
     * Deletes group from lab
     *
     * @param int $labId        The lab identifier
     * @param int groupId       The group identifier
     */
    public function deleteGroupForLab($labId, $groupId)
    {
        LabGroup::where('lab_id', $labId)
            ->where('group_id', $groupId)
            ->delete();
    }

    /**
     * Gets all groups for given course
     *
     * @param int $courseId     The course identifier
     * @return []               Groups.
     */
    public function getAllGroups($courseId)
    {
        return Group::where('courseid', $courseId)
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Gets all groupings for given course
     *
     * @param int $courseId     The course identifier
     * @return []               Groupings.
     */
    public function getAllGroupings($courseId)
    {
        return Grouping::where('courseid', $courseId)
            ->join('groupings_groups', 'groupingid', 'groupings.id')
            ->select('groupings.id', 'groupings.name', 'groupings_groups.groupid')
            ->orderBy('groupings.name', 'asc')
            ->get();
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
