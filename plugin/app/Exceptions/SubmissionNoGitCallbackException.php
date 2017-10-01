<?php

namespace TTU\Charon\Exceptions;

/**
 * Class ResultPointsRequiredException.
 * When result is saved and no calculated result is set.
 *
 * @package TTU\Charon\Exceptions
 */
class SubmissionNoGitCallbackException extends BadRequestException
{
    /** @var int */
    public $submissionId;

    /**
     * @param  int  $submissionId
     *
     * @return $this
     */
    public function setSubmissionId($submissionId)
    {
        $this->submissionId = $submissionId;

        return $this;
    }
}
