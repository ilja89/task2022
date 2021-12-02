<?php

namespace TTU\Charon\Exceptions;

/**
 * Class IncorrectRegistrationException.
 *
 * @package TTU\Charon\Exceptions
 */
class IncorrectRegistrationException extends CharonException
{
    /**
     * RegistrationException constructor.
     */
    public function __construct($message)
    {
        return parent::__construct($message);
    }
}
