<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Models\User;

class SubmissionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var SubmissionsRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new SubmissionsRepository(new MoodleConfig());
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
}
