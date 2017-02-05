<?php

namespace TTU\Charon\Exceptions;

/**
 * Class ForbiddenException.
 *
 * Thrown when the accessing user does not have permissions to
 * do the activity.
 *
 * This should be extended into more specific exceptions
 * i.e. IncorrectSecretTokenException.
 *
 * @package TTU\Charon\Exceptions
 */
class ForbiddenException extends CharonException
{
    protected $status = 403;

    /**
     * NotFoundException constructor.
     */
    public function __construct()
    {
        $message = $this->build(func_get_args());

        return parent::__construct($message);
    }
}
