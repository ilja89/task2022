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

    /** @var integer */
    public $submissionCreation;

    /** @var string */
    public $path;

    /** @var array */
    public $reviewComments = [];

    function __construct($fileId, $charonId, $submissionId, $created_at, $studentId, $path, $reviewComment)
    {
        $this->fileId = $fileId;
        $this->charonId = $charonId;
        $this->submissionId = $submissionId;
        $this->submissionCreation = $created_at;
        $this->studentId = $studentId;
        $this->path = $path;
        array_push($this->reviewComments, $reviewComment);
    }
}
