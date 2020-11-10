<?php

namespace Tests\Integration\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\GitCallbacksRepository;
use TTU\Charon\Services\GitCallbackService;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\Group;
use Zeizig\Moodle\Models\Grouping;
use Zeizig\Moodle\Models\User;

class GitCallbackServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GitCallbackService */
    private $service;

    /** @var GitCallbacksRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = Mockery::mock(GitCallbacksRepository::class);
        $this->service = new GitCallbackService($this->repository);
    }

    public function testGetCourseReturnsCourseOnFullMatch()
    {
        factory(Course::class)->create(['shortname' => 'direct-match']);

        $course = $this->service->getCourse('git@gitlab.cs.ttu.ee:username/direct-match.git');

        $this->assertEquals('direct-match', $course->shortname);
    }

    public function testGetCourseReturnsCourseFromSplitName()
    {
        factory(Course::class)->create(['shortname' => 'longer']);

        $course = $this->service->getCourse('git@gitlab.cs.ttu.ee:username/longer-project-name.git');

        $this->assertEquals('longer', $course->shortname);
    }

    public function testGetCourseReturnsNullIfNoMatch()
    {
        $course = $this->service->getCourse('git@gitlab.cs.ttu.ee:username/longer-project-name.git');

        $this->assertNull($course);
    }

    public function testFindCharonsReturnsOnlyCharonsMatchingModifiedFolders()
    {
        $create = function ($folder): Charon {
            return factory(Charon::class)->create(['project_folder' => $folder, 'course' => 1, 'category_id' => 1]);
        };

        $wrongFolder = $create('ignored');
        $tooShort = $create('tooShort');
        $firstMatch = $create('part1/part2');
        $secondMatch = $create('second\\match\\');
        $mixedMatch = $create('mixed\\match\\');

        $files = [
            'too',
            'part1',
            'part1/part2/duplicate',
            'second\\match\\with\\backslash',
            'mixed/match/with/mixed/slashes',
        ];

        $actual = $this->service->findCharons($files, 1);

        $ids = collect($actual)->pluck('id')->all();

        $this->assertContains($firstMatch->id, $ids);
        $this->assertContains($secondMatch->id, $ids);
        $this->assertContains($mixedMatch->id, $ids);
        $this->assertNotContains($wrongFolder->id, $ids);
        $this->assertNotContains($tooShort->id, $ids);
    }

    public function testGetGroupUsersReturnsEmptyWhenNoGroupFound()
    {
        $actual = $this->service->getGroupUsers(PHP_INT_MAX, 'username');

        $this->assertEmpty($actual);
    }

    public function testGetGroupUsersReturnsEmptyWhenInitiatorNotFound()
    {
        /** @var Grouping $grouping */
        $grouping = factory(Grouping::class)->create();

        $actual = $this->service->getGroupUsers($grouping->id, 'username');

        $this->assertEmpty($actual);
    }

    public function testGetGroupUsersReturnsEmptyWhenNoGroupsAreFound()
    {
        /** @var Grouping $grouping */
        $grouping = factory(Grouping::class)->create();

        factory(User::class)->create(['username' => 'username@ttu.ee']);

        $actual = $this->service->getGroupUsers($grouping->id, 'username');

        $this->assertEmpty($actual);
    }

    public function testGetGroupUsersReturnsJustInitiatorWhenTooManyGroupsAreFound()
    {
        /** @var Grouping|Model $grouping */
        $grouping = factory(Grouping::class)->create();

        $user = factory(User::class)->create(['username' => 'username@ttu.ee']);

        /** @var Group|Model $group1 */
        $group1 = factory(Group::class)->create();

        /** @var Group|Model $group2 */
        $group2 = factory(Group::class)->create();

        $group1->members()->save($user);
        $group2->members()->save($user);

        $grouping->groups()->saveMany([$group1, $group2]);

        $actual = $this->service->getGroupUsers($grouping->id, 'username');

        $this->assertContains('username', $actual);
    }

    public function testGetGroupUsersReturnsUsernamesWhenOneGroupIsFound()
    {
        /** @var Grouping|Model $grouping */
        $grouping = factory(Grouping::class)->create();

        $user1 = factory(User::class)->create(['username' => 'username@ttu.ee']);
        $user2 = factory(User::class)->create(['username' => 'second@ttu.ee']);
        $user3 = factory(User::class)->create(['username' => 'third@ttu.ee']);

        /** @var Group|Model $group */
        $group = factory(Group::class)->create();

        $group->members()->saveMany([$user1, $user2, $user3]);

        $grouping->groups()->save($group);

        $actual = $this->service->getGroupUsers($grouping->id, 'username');

        $this->assertContains('username', $actual);
        $this->assertContains('second', $actual);
        $this->assertContains('third', $actual);
    }
}
