<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use TTU\Charon\Models\Comment;

/**
 * Class CommentsRepository.
 *
 * @package TTU\Charon\Repositories
 */
class CommentsRepository
{
    /**
     * Save the given comment and return it. Also load the commenting teacher's data.
     *
     * @param  int  $charonId
     * @param  int  $studentId
     * @param  int  $teacherId
     * @param  string  $commentText
     *
     * @return Comment
     */
    public function saveComment($charonId, $studentId, $teacherId, $commentText)
    {
        $comment = Comment::create([
            'charon_id'  => $charonId,
            'student_id' => $studentId,
            'teacher_id' => $teacherId,
            'message'    => $commentText,
            'created_at' => Carbon::now(),
        ]);

        $comment->load([
            'teacher' => function ($query) {
                $query->select(['id', 'firstname', 'lastname']);
            },
        ]);

        return $comment;
    }

    /**
     * Finds comments by the given Charon and student. Also loads commenter
     * info.
     *
     * @param  int  $charonId
     * @param  int  $studentId
     *
     * @return Collection|Comment[]
     */
    public function findCommentsByCharonAndStudent($charonId, $studentId)
    {
        return Comment::with([
                'teacher' => function ($query) {
                    $query->select(['id', 'firstname', 'lastname']);
                },
            ])
            ->where('student_id', $studentId)
            ->where('charon_id', $charonId)
            ->get();
    }
}
