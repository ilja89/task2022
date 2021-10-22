<?php

namespace TTU\Charon\Exceptions;

/**
 * Class ReviewCommentException.
 *
 * @package TTU\Charon\Exceptions
 */
class ReviewCommentException extends CharonException
{
    protected $status = 400;

    /**
     * RegistrationException constructor.
     */
    public function __construct()
    {
        $message = $this->build(func_get_args());

        return parent::__construct($message);
    }
}
