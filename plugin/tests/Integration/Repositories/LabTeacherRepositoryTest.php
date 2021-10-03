<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Registration;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\LabTeacherRepository;
use Tests\TestCase;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

class LabTeacherRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabTeacherRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $config = new MoodleConfig();
        $config->prefix = 'mdl_';
        $this->repository = new LabTeacherRepository($config);
    }

    public function testCheckWhichTeachersBusyAt()
    {
        /** @var User $teacher1 */
        $teacher1 = factory(User::class)->create();

        /** @var User $teacher2 */
        $teacher2 = factory(User::class)->create();

        /** @var User $teacher3 */
        $teacher3 = factory(User::class)->create();

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Lab $lab */
        $lab = factory(Lab::class)->create();

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var CharonDefenseLab $defenseLab */
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => $charon->id]);

        $common = [
            'student_id' => $student->id,
            'charon_id' => $charon->id,
            'defense_lab_id' => $defenseLab->id,
            'progress' => 'Waiting'
        ];

        $chosenTime = Carbon::now()->addMinutes(20);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(10),
            'teacher_id' => $teacher1->id
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => $chosenTime,
            'teacher_id' => $teacher2->id
        ]);

        factory(Registration::class)->create($common + [
            'choosen_time' => Carbon::now()->addMinutes(30),
            'teacher_id' => $teacher3->id
        ]);

        $actual = $this->repository->checkWhichTeachersBusyAt(
            [$teacher1->id, $teacher2->id, $teacher3->id],
            $chosenTime
        );

        $this->assertEquals([$teacher2->id], $actual);
    }

    public function testGetTeacherReportByCourseIdCountsDefensesByCourse()
    {
        $teacherRoleId = 3;

        /** @var User $mathTeacher */
        $mathTeacher = factory(User::class)->create(['firstname' => 'Mary', 'lastname' => 'Celeste']);

        /** @var User $substituteTeacher */
        $substituteTeacher = factory(User::class)->create(['firstname' => 'Jack', 'lastname' => 'AllTrade']);

        /** @var Course $math */
        $math = factory(Course::class)->create();

        /** @var Course $bio */
        $bio = factory(Course::class)->create();

        $mathContextId = DB::table('context')->insertGetId(['instanceid' => $math->id]);
        $bioContextId = DB::table('context')->insertGetId(['instanceid' => $bio->id]);

        DB::table('role_assignments')->insert([
            ['roleid' => $teacherRoleId, 'contextid' => $mathContextId, 'userid' => $mathTeacher->id],
            ['roleid' => $teacherRoleId, 'contextid' => $mathContextId, 'userid' => $substituteTeacher->id],
            ['roleid' => $teacherRoleId, 'contextid' => $bioContextId, 'userid' => $substituteTeacher->id],
        ]);

        /** @var Charon $calcHomework */
        $calcHomework = factory(Charon::class)->create(['course' => $math->id, 'category_id' => 0]);

        /** @var Charon $floraHomework */
        $floraHomework = factory(Charon::class)->create(['course' => $bio->id, 'category_id' => 0]);

        /** @var Charon $faunaHomework */
        $faunaHomework = factory(Charon::class)->create(['course' => $bio->id, 'category_id' => 0]);

        /** @var User $student */
        $student = factory(User::class)->create();

        factory(Submission::class)->create([
            'charon_id' => $calcHomework->id,
            'user_id' => $student->id,
            'confirmed' => 1,
            'grader_id' => $mathTeacher->id
        ]);

        factory(Submission::class)->create([
            'charon_id' => $calcHomework->id,
            'user_id' => $student->id,
            'confirmed' => 0,
            'grader_id' => $mathTeacher->id
        ]);

        factory(Submission::class)->create([
            'charon_id' => $calcHomework->id,
            'user_id' => $student->id,
            'confirmed' => 1,
            'grader_id' => $substituteTeacher->id
        ]);

        factory(Submission::class)->create([
            'charon_id' => $floraHomework->id,
            'user_id' => $student->id,
            'confirmed' => 1,
            'grader_id' => $substituteTeacher->id
        ]);

        factory(Submission::class)->create([
            'charon_id' => $faunaHomework->id,
            'user_id' => $student->id,
            'confirmed' => 1,
            'grader_id' => $substituteTeacher->id
        ]);

        $mathTeachers = $this->repository->getTeacherReportByCourseId($math->id);
        $bioTeachers = $this->repository->getTeacherReportByCourseId($bio->id);

        $this->assertEquals(2, sizeof($mathTeachers));
        $this->assertEquals(1, $mathTeachers->firstWhere('fullname', 'Mary Celeste')->total_defences);
        $this->assertEquals(1, $mathTeachers->firstWhere('fullname', 'Jack AllTrade')->total_defences);

        $this->assertEquals(1, sizeof($bioTeachers));
        $this->assertEquals(2, $bioTeachers->firstWhere('fullname', 'Jack AllTrade')->total_defences);
    }
}
