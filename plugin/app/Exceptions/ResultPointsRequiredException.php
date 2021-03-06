<?php

namespace TTU\Charon\Exceptions;

/**
 * Class ResultPointsRequiredException.
 * When result is saved and no calculated result is set.
 *
 * @package TTU\Charon\Exceptions
 */
class ResultPointsRequiredException extends BadRequestException
{
    /** @var int */
    public $resultId;

    /**
     * @param  int  $resultId
     *
     * @return $this
     */
    public function setResultId($resultId)
    {
        $this->resultId = $resultId;

        return $this;
    }
}
