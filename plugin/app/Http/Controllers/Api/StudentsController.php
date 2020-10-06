<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
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

    /**
     * StudentsController constructor.
     *
     * @param Request $request
     * @param StudentsRepository $studentsRepository
     * @param GradebookService $gradebookService
     */
    public function __construct(Request $request, StudentsRepository $studentsRepository, GradebookService $gradebookService)
    {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->gradebookService = $gradebookService;
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

        $users = $this->studentsRepository->searchStudentsByCourseAndKeyword($course->id, $keyword);

        return $users;
    }

    /**
     * Find the user by the given ID.
     *
     * @param Course $course
     * @param int $userId
     *
     * @return User
     */
    public function findById(Course $course, $userId)
    {
        return User::where('id', $userId)
            ->first(['id', 'firstname', 'lastname', 'idnumber', 'username']);
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
        global $CFG;
        require_once $CFG->dirroot . '/grade/lib.php';
        require_once $CFG->dirroot . '/grade/report/user/lib.php';

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

    public function getStudentGroups(Course $course, int $userId)
    {
        return $this->findById($course, $userId)->groups()->with('members:idnumber,firstname,lastname,username')->where('courseid', $course->id)->get();
    }


    public function getStudentInfo(Course $course, $userId)
    {
        $userId = (int)$userId;
        $student = $this->findById($course, $userId);
        $student['groups'] = $this->getStudentGroups($course, $userId);
        $student['totalPoints'] = $this->getStudentTotalGrade($course, $userId);
        return $student;
    }

    public function getStudentTotalGrade(Course $course, int $userId)
    {
        $gradeItem = GradeItem::where(array('courseid' => $course->id, 'itemtype' => 'course'))->first();
        $grade = GradeGrade::where(array('itemid' => $gradeItem->id, 'userid' => $userId))->first();
        if (isset($grade->finalgrade)) {
            return floatval($grade->finalgrade);
        } else {
            return 0;
        }
    }


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

        $users = Submission::with([
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

        return $users;
    }

    public function findDistribution(Course $course)
    {
        global $CFG;
        $prefix = $CFG->prefix;

        $parts = 5;
        $sql = 'select
                floor((gg.finalgrade * ?) / (max_grades.max_grade + 0.1)) as part,
                count(gg.userid) as user_count,
                max_grades.max_grade as max_grade
            from
                ' . $prefix . 'grade_grades gg
            inner join
                ' . $prefix . 'grade_items gi
                on
                    gg.itemid = gi.id
            inner join
                (
                    select
                        max(gg_inner.finalgrade) as max_grade,
                        gi_inner.courseid as course_id
                    from
                      ' . $prefix . 'grade_items as gi_inner
                      inner join ' . $prefix . 'grade_grades as gg_inner
                          on gi_inner.id = gg_inner.itemid
                    where gi_inner.itemtype = \'course\'
                    and gi_inner.courseid = ?
                    group by gi_inner.id, gi_inner.courseid
                ) as max_grades
                on max_grades.course_id = gi.courseid
            where gi.courseid = ?
            and gi.categoryid is null
            and gi.itemtype = \'course\'
            and gg.userid is not null
            group by 1, max_grade
        ';

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
}
