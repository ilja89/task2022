<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Models\User;

class SubmissionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var SubmissionsRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var Mock|MoodleConfig $config */
        $config = $this->app->make(MoodleConfig::class);
        $config->prefix = 'mdl_';
        $this->repository = new SubmissionsRepository($config);
    }

    public function testFindUserSubmissions()
    {
        /** @var User $student1 */
        $student1 = factory(User::class)->create();
        /** @var User $student2 */
        $student2 = factory(User::class)->create();

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var Submission $submission1 */
        $submission1 = factory(Submission::class)->create(['charon_id' => $charon->id]);
        $submission1->users()->saveMany([$student1, $student2]);

        /** @var Submission $submission2 */
        $submission2 = factory(Submission::class)->create(['charon_id' => $charon->id]);
        $submission2->users()->saveMany([$student1, $student2]);

        /** @var Submission $submission3 */
        $submission3 = factory(Submission::class)->create(['charon_id' => $charon->id]);
        $submission3->users()->save($student2);

        $submissions = $this->repository->findUserSubmissions($student1->id);

        $this->assertEquals(2, $submissions->count());

        $submissions = $this->repository->findUserSubmissions($student2->id);

        $this->assertEquals(3, $submissions->count());
        $this->assertContainsOnlyInstancesOf(Submission::class, $submissions);
    }

    public function testCarryPersistentResult()
    {
        $this->markTestSkipped('Out of date, needs attention');

        $now = Carbon::now()->format('Y-m-d H:i:s');

        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var User $student */
        $student = factory(User::class)->create();

        /** @var Submission $withoutResult */
        $withoutResult = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'grader_id' => $student->id,
            'confirmed' => 1,
            'updated_at' => $now
        ]);
        $withoutResult->users()->save($student);

        /** @var Submission $matchingLatest */
        $matchingLatest = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'grader_id' => $student->id,
            'confirmed' => 1,
            'updated_at' => $now
        ]);
        $matchingLatest->users()->save($student);

        Result::create([
            'submission_id' => $matchingLatest->id,
            'grade_type_code' => 101,
            'percentage' => 0.75,
            'calculated_result' => 0.75
        ]);

        $previous = Result::create([
            'submission_id' => $matchingLatest->id,
            'grade_type_code' => 1001,
            'percentage' => 0.50,
            'calculated_result' => 0.50
        ]);

        /** @var Submission $matchingOld */
        $matchingOld = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'grader_id' => $student->id,
            'confirmed' => 1,
            'updated_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s')
        ]);
        $matchingOld->users()->save($student);

        Result::create([
            'submission_id' => $matchingOld->id,
            'grade_type_code' => 1001,
            'percentage' => 0.99,
            'calculated_result' => 0.99
        ]);

        /** @var Submission $unconfirmed */
        $unconfirmed = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'grader_id' => $student->id,
            'confirmed' => 0,
            'updated_at' => Carbon::now()->addDay()->format('Y-m-d H:i:s')
        ]);
        $unconfirmed->users()->save($student);

        Result::create([
            'submission_id' => $unconfirmed->id,
            'grade_type_code' => 1001,
            'percentage' => 0.55,
            'calculated_result' => 0.55
        ]);

        /** @var Submission $notGraded */
        $notGraded = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'confirmed' => 1,
            'updated_at' => Carbon::now()->addDay()->format('Y-m-d H:i:s')
        ]);
        $notGraded->users()->save($student);

        Result::create([
            'submission_id' => $notGraded->id,
            'grade_type_code' => 1001,
            'percentage' => 0.99,
            'calculated_result' => 0.99
        ]);

        /** @var Submission $current */
        $current = factory(Submission::class)->create(['charon_id' => $charon->id]);
        $current->users()->save($student);

        $this->repository->carryPersistentResult($current->id, $student->id, $charon->id, 1001);

        $result = Result::where('submission_id', $current->id)->where('grade_type_code', 1001)->first();

        $this->assertEquals(0.50, $result->percentage);
        $this->assertEquals(0.50, $result->calculated_result);
        $this->assertEquals('Carried over from Result ' . $previous->id, $result->stdout);
    }

    public function testFindLatestByCharonReturnsForEveryStudent()
    {
        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var User $student1 */
        $student1 = factory(User::class)->create();

        /** @var User $student2 */
        $student2 = factory(User::class)->create();

        /** @var User $student3 */
        $student3 = factory(User::class)->create();

        /** @var Submission $newSubmission */
        $newSubmission = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'user_id' => $student2->id,
            'created_at' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')
        ]);

        /** @var Submission $oldSubmission */
        $oldSubmission = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'user_id' => $student2->id,
            'created_at' => Carbon::now()->subDays(5)->format('Y-m-d H:i:s')
        ]);

        /** @var Submission $oldestSubmission */
        $oldestSubmission = factory(Submission::class)->create([
            'charon_id' => $charon->id,
            'user_id' => $student2->id,
            'created_at' => Carbon::now()->subDays(7)->format('Y-m-d H:i:s')
        ]);

        $newSubmission->users()->saveMany([$student1, $student2]);

        $oldSubmission->users()->saveMany([$student1, $student2, $student3]);

        $oldestSubmission->users()->saveMany([$student2, $student3]);

        $actual = $this->repository->findLatestByCharon($charon->id);

        $this->assertEquals([$newSubmission->id, $oldSubmission->id], $actual);
    }

    public function testLatestSubmissionsForCharon() {

        /** @var Charon $charon1 */
        $charon1 = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);
        /** @var Charon $charon2 */
        $charon2 = factory(Charon::class)->create(['course' => 1, 'category_id' => 1]);

        /** @var Submission $submission1 */
        $submission1 = factory(Submission::class)->create(['charon_id' => $charon1->id]);

        /** @var Submission $submission2 */
        $submission2 = factory(Submission::class)->create(['charon_id' => $charon2->id]);

        /** @var Submission $submission3 */
        $submission3 = factory(Submission::class)->create(['charon_id' => $charon2->id]);

        $submissions1 = $this->repository->findLatestSubmissionsForCharon($charon1->id);

        $this->assertEquals(1, $submissions1->count());

        $submissions2 = $this->repository->findLatestSubmissionsForCharon($charon2->id);

        $this->assertEquals(2, $submissions2->count());
    }
}
