<?php

namespace TTU\Charon\Dto;

use Illuminate\Database\Eloquent\Model;

/**
 * DTO helper for ReviewComment listing under submission file.
 */
class FileReviewCommentsDTO
{
    /** @var integer */
    public $fileId;

    /** @var integer */
    public $charonId;

    /** @var integer */
    public $studentId;

    /** @var integer */
    public $submissionId;

    /** @var string */
    public $path;

    /** @var array */
    public $reviewComments = [];
}
