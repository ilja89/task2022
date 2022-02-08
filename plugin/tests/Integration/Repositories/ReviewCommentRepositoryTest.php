<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\ReviewComment;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use Tests\TestCase;
use TTU\Charon\Repositories\ReviewCommentRepository;
use Zeizig\Moodle\Models\User;


class ReviewCommentRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var ReviewCommentRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ReviewCommentRepository();
    }

    public function testAddSuccessfully()
    {
        $this->repository->add(2, 2, 'UniqueComment', false);

        $actual = ReviewComment::where('review_comment', 'UniqueComment')->first();

        $this->assertNotNull($actual);
        $this->assertEquals('UniqueComment', $actual->review_comment);
    }

    public function testGetSuccessfully()
    {
        $reviewComment = $this->createReviewComment();

        $actual = $this->repository->get($reviewComment->id);

        $this->assertEquals($reviewComment->id, $actual->id);
    }

    public function testDeleteSuccessfully()
    {
        $reviewComment = $this->createReviewComment();

        $actual = $this->repository->delete($reviewComment->id);

        $this->assertTrue($actual);
    }

    public function testClearNotificationSuccesfully()
    {
        $reviewComment = $this->createReviewComment();

        $didUpdate = $this->repository->clearNotification([$reviewComment->id]);

        $this->assertTrue($didUpdate);

        $actual = $this->repository->get($reviewComment->id);

        $this->assertEquals(0, $actual->notify);
    }

    public function  testGetReviewCommentsForCharonAndStudentSuccessfully()
    {
        /** @var User $user */
        $user = User::create([
            'firstname' => 'Jaan',
            'lastname' => 'Juurikas',
            'username' => 'jajuur@ttu.ee'
        ]);

        /** @var User $associatedUser */
        $associatedUser = User::create([
            'firstname' => 'Mc',
            'lastname' => 'Juurikas',
            'username' => 'mcjuur@ttu.ee'
        ]);

        $charonId = 1;

        /** @var Submission $submission */
        $submission = Submission::create([
            'charon_id' => $charonId,
            'user_id' => $user->id,
            'git_timestamp' => Carbon::now()
        ]);

        DB::table('charon_submission_user')->insert(
            [
                'submission_id' => $submission->id,
                'user_id' => $user->id
            ]
        );

        DB::table('charon_submission_user')->insert(
            [
                'submission_id' => $submission->id,
                'user_id' => $associatedUser->id
            ]
        );

        /** @var SubmissionFile $submissionFile */
        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'path' => 'tests.py',
            'contents' => 'print("hello")'
        ]);

        /** @var ReviewComment $reviewComment */
        $reviewComment = ReviewComment::create([
            'user_id' => $user->id,
            'submission_file_id' => $submissionFile->id,
            'review_comment' => 'comment'
        ]);

        /** @var ReviewComment $reviewComment2 */
        $reviewComment2 = ReviewComment::create([
            'user_id' => $user->id,
            'submission_file_id' => $submissionFile->id,
            'review_comment' => 'comment2',
            'notify' => 1
        ]);

        $result = $this->repository->getReviewCommentsForCharonAndStudent($charonId, $user->id);

        $result2 = $this->repository->getReviewCommentsForCharonAndStudent($charonId, $associatedUser->id);

        $this->assertEquals($charonId, $result[0]->charonId);
        $this->assertEquals($submission->id, $result[0]->submissionId);
        $this->assertEquals($submissionFile->id, $result[0]->fileId);
        $this->assertEquals($user->id, $result[0]->studentId);
        $this->assertEquals($reviewComment2->id, $result[0]->reviewComments[0]->id);
        $this->assertEquals($reviewComment2->notify, $result[0]->reviewComments[0]->notify);
        $this->assertEquals($reviewComment->id, $result[0]->reviewComments[1]->id);
        $this->assertEquals($reviewComment->notify, $result[0]->reviewComments[1]->notify);

        $this->assertEquals($result[0]->charonId, $result2[0]->charonId);
        $this->assertEquals($result[0]->submissionId, $result2[0]->submissionId);
        $this->assertEquals($result[0]->fileId, $result2[0]->fileId);
        $this->assertEquals($result[0]->studentId, $result2[0]->studentId);
        $this->assertEquals($result[0]->reviewComments[0]->id, $result2[0]->reviewComments[0]->id);
        $this->assertEquals($result[0]->reviewComments[0]->notify, $result2[0]->reviewComments[0]->notify);
        $this->assertEquals($result[0]->reviewComments[1]->id, $result2[0]->reviewComments[1]->id);
        $this->assertEquals($result[0]->reviewComments[1]->notify, $result2[0]->reviewComments[1]->notify);
    }

    private function createReviewComment()
    {
        return ReviewComment::create([
            'user_id' => 2,
            'submission_file_id' => 2,
            'code_row_no_start' => null,
            'code_row_no_end' => null,
            'review_comment' => 'reviewComment',
            'created_at' => Carbon::now(),
            'notify' => 1,
        ]);
    }
}
