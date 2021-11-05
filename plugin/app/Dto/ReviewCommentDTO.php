<?php

namespace TTU\Charon\Dto;

/**
 * DTO helper for ReviewComment.
 */
class ReviewCommentDTO
{
    /** @var integer */
    public $id;

    /** @var integer */
    public $commentedById;

    /** @var string */
    public $commentedByFirstName;

    /** @var string */
    public $commentedByLastName;

    /** @var integer */
    public $codeRowNoStart;

    /** @var integer */
    public $codeRowNoEnd;

    /** @var string */
    public $reviewComment;

    /** @var integer */
    public $notify;

    /** @var integer */
    public $commentCreation;

    function __construct($id, $commentedById, $firstName, $lastName, $start, $end, $reviewComment, $notify, $created)
    {
        $this->id = $id;
        $this->commentedById = $commentedById;
        $this->commentedByFirstName = $firstName;
        $this->commentedByLastName = $lastName;
        $this->codeRowNoStart = $start;
        $this->codeRowNoEnd = $end;
        $this->reviewComment = $reviewComment;
        $this->notify = $notify;
        $this->commentCreation = $created;
    }
}
