<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\CommentsRepository;
use Zeizig\Moodle\Globals\User;

/**
 * Class CommentsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class CommentsController extends Controller
{
    /** @var CommentsRepository */
    private $commentsRepository;

    /** @var User */
    private $user;

    /**
     * CommentsController constructor.
     *
     * @param Request $request
     * @param CommentsRepository $commentsRepository
     * @param User $user
     */
    public function __construct(Request $request, CommentsRepository $commentsRepository, User $user)
    {
        parent::__construct($request);
        $this->commentsRepository = $commentsRepository;
        $this->user = $user;
    }

    /**
     * Get comments by the charon and student from request.
     *
     * @param Charon $charon
     *
     * @return \Illuminate\Database\Eloquent\Collection|\TTU\Charon\Models\Comment[]
     */
    public function getByCharonAndStudent(Charon $charon)
    {
        $comments = $this->commentsRepository->findCommentsByCharonAndStudent(
            $charon->id,
            $this->request['student_id']
        );

        return $comments;
    }

    /**
     * Saves a comment. Comment details are taken from the request.
     *
     * @param  Charon $charon
     *
     * @return array
     */
    public function saveComment(Charon $charon)
    {
        $comment = $this->commentsRepository->saveComment(
            $charon->id,
            $this->request['student_id'],
            $this->user->currentUserId(),
            $this->request['comment']
        );

        return [
            'status'  => 'OK',
            'comment' => $comment,
        ];
    }
}
