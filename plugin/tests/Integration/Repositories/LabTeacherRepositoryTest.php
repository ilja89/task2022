<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
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
