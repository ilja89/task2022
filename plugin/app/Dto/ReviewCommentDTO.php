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
    public $reviewCommentId;

    /** @var integer */
    public $commentCreation;

    /** @var integer */
    public $notify;

    /** @var string */
    public $review_comment;

    /** @var integer */
    public $codeRowNoStart;

    /** @var integer */
    public $codeRowNoEnd;

    /** @var integer */
    public $commentedById;

    /** @var string */
    public $commentedByFirstName;

    /** @var string */
    public $commentedByLastName;
}