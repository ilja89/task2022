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
     * @param array $collection
     */
    public function createManyLabGroups(array $collection)
    {
        LabGroup::insert($collection);
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
     * @param integer $id
     *
     * @return Lab
     * @throws Exception
     */
    public function deleteByInstanceId($id): Lab
    {
        /** @var Lab $lab */
        $lab = Lab::find($id);

        DefenseRegistration::where('lab_id', $id)->delete();
        CharonDefenseLab::where('lab_id', $id)->delete();
        LabTeacher::where('lab_id', $id)->delete();

        $lab->delete();
        return $lab;
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
            ->select('id', 'start', 'end', 'name', 'course_id', 'chunk_size')
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
     * @version Registration 2.*
     * 
     * @param int $labId
     * @param string $start
     * @param string $end
     * @param int[] $charons
     * @param int[] $teachers
     * @param bool $delete
     * @return int
     * 
     */
    public function countRegistrations($labId, $start=null, $end=null, $charons=null, $teachers=null, $delete=false)
    {
        $records = \DB::table('charon_defense_registration')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defense_registration.lab_id')
            ->where('charon_defense_lab.lab_id', $labId)
            ->whereNotIn('progress',  ['Done', 'New'])
            ->where(function($q) use($start, $end, $charons, $teachers) {
                if ($start) {
                    $q = $q->orWhere('time', '<', $start);
                }
                if ($end) {
                    $q = $q->orWhere('time', '>', $end);
                }
                if ($charons) {
                    $q = $q->orWhereIn('charon_defense_registration.charon_id', $charons);
                }
                if ($teachers) {
                    $q = $q->orWhereIn('charon_defense_registration.teacher_id', $teachers);
                }
            });

        $count = $records->count();

        if ($delete && $count > 0) {
            $records->delete();
            Log::info('Deleted ' . $count . " registrations");
        }

        return $count;
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
}
