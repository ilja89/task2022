<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class StudentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class StudentsController extends Controller
{
    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var GradebookService */
    private $gradebookService;

    /** @var MoodleConfig */
    private $moodleConfig;

    /**
     * StudentsController constructor.
     *
     * @param Request $request
     * @param StudentsRepository $studentsRepository
     * @param GradebookService $gradebookService
     * @param MoodleConfig $moodleConfig
     */
    public function __construct(
        Request $request,
        StudentsRepository $studentsRepository,
        GradebookService $gradebookService,
        MoodleConfig $moodleConfig
    )
    {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->gradebookService = $gradebookService;
        $this->moodleConfig = $moodleConfig;
    }

    /**
     * Search students by the given keyword.
     *
     * @param Course $course
     *
     * @return Collection
     */
    public function searchStudents(Course $course)
    {
        $keyword = $this->request['q'];

        return $this->studentsRepository->searchStudentsByCourseAndKeyword($course->id, $keyword);
    }

    /**
     * Get active result for the given charon and user. Takes the value
     * from gradebook.
     *
     * @param Charon $charon
     * @param User $user
     *
     * @return float
     */
    public function getStudentActiveResultForCharon(Charon $charon, User $user)
    {
        $categoryGradeItem = $this->gradebookService->getGradeItemByCategoryId($charon->category_id);
        $categoryGradeGrade = $this->gradebookService->getGradeForGradeItemAndUser($categoryGradeItem->id, $user->id);

        if ($categoryGradeGrade !== null) {
            return $categoryGradeGrade->finalgrade;
        } else {
            return 0;
        }
    }

    public function getStudentReportTable(Course $course, User $user)
    {
        global $CFG; // grade/lib.php needs it
        require_once $this->moodleConfig->dirroot . '/grade/lib.php';
        require_once $this->moodleConfig->dirroot . '/grade/report/user/lib.php';

        /// return tracking object
        $gpr = new \grade_plugin_return(array('type' => 'report', 'plugin' => 'user', 'courseid' => $course->id, 'userid' => $user->id));
        $context = \context_course::instance($course->id);

        // Create a report instance
        $report = new \grade_report_user($course->id, $gpr, $context, $user->id);

        if ($report->fill_table()) {
            return $report->print_table(true);
        }
        return '';
    }


    public function getStudentInfo(Course $course, $userId)
    {
        $userId = (int)$userId;
        $student = $this->studentsRepository->findById($userId);
        try {
            $student['groups'] = $this->studentsRepository->getStudentGroups($course, $userId);
        } catch (\Exception $e) {
            $student['groups'] = [];
        }
        $student['totalPoints'] = $this->studentsRepository->getStudentTotalGrade($course, $userId);
        return $student;
    }

    /**
     * Currently this counts only students pushing the submissions but omits participants in group works.
     *
     * @param Course $course
     *
     * @return Collection
     */
    public function findActive(Course $course)
    {
        $period = $this->request->query('period');

        $startTime = Carbon::now();
        if ($period === 'day') {
            $startTime = $startTime->subDay();
        } else if ($period === 'week') {
            $startTime = $startTime->subWeek();
        } else if ($period === 'month') {
            $startTime = $startTime->subMonth();
        }

        return Submission::with([
            'user' => function ($query) {
                $query->select(['id', 'firstname', 'lastname']);
            },
        ])
            ->whereHas('charon', function ($query) use ($course) {
                $query->where('course', $course->id);
            })
            ->where('created_at', '>=', $startTime)
            ->get()
            ->pluck('user')
            ->unique()
            ->values();
    }

    public function findDistribution(Course $course)
    {
        $prefix = $this->moodleConfig->prefix;

        $parts = 5;
        $sql = "select
                floor((gg.finalgrade * ?) / (max_grades.max_grade + 0.1)) as part,
                count(gg.userid) as user_count,
                max_grades.max_grade as max_grade
            from
                " . $prefix . "grade_grades gg
            inner join
                " . $prefix . "grade_items gi
                on
                    gg.itemid = gi.id
            inner join
                (
                    select
                        max(gg_inner.finalgrade) as max_grade,
                        gi_inner.courseid as course_id
                    from
                      " . $prefix . "grade_items as gi_inner
                      inner join " . $prefix . "grade_grades as gg_inner
                          on gi_inner.id = gg_inner.itemid
                    where gi_inner.itemtype = 'course'
                    and gi_inner.courseid = ?
                    group by gi_inner.id, gi_inner.courseid
                ) as max_grades
                on max_grades.course_id = gi.courseid
            where gi.courseid = ?
            and gi.categoryid is null
            and gi.itemtype = 'course'
            and gg.userid is not null
            group by 1, max_grade
        ";

        $result = DB::select($sql, [$parts, $course->id, $course->id]);

        $studentsDistribution = Collection::make($result);
        $maxResult = $studentsDistribution->isEmpty() ? 0 : $studentsDistribution->first()->max_grade;
        for ($i = 0; $i < $parts; $i++) {
            $contains = $studentsDistribution->contains('part', $i);
            if (!$contains) {
                $distribution = [
                    'max_grade' => $maxResult,
                    'part' => $i,
                    'user_count' => 0,
                ];
                $studentsDistribution->push($distribution);
            }
        }

        return $studentsDistribution;
    }

    /**
     * Get data from all charons by user and course.
     *
     * @param int $courseId
     * @param int $userId
     *
     * @return array
     */
    public function getUserCharonsDetails($courseId, $userId)
    {
        return $this->studentsRepository->getUserCharonsDetails($courseId, $userId);
    }

    public function getAllStudents(Course $course) {
        return $this->studentsRepository->getAllByCourse($course->id);
    }
}
