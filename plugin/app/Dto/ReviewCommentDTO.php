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







    /** @var string */
    public $commentedByFirstName;

    /** @var string */
    public $commentedByLastName;
}
