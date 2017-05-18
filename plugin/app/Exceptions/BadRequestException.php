<?php

namespace TTU\Charon\Exceptions;

/**
 * Class BadRequestException.
 * Some error with the request.
 *
 * @package TTU\Charon\Exceptions
 */
class BadRequestException extends CharonException
{
    protected $status = 400;

    /**
     * NotFoundException constructor.
     */
    public function __construct()
    {
        $message = $this->build(func_get_args());

        return parent::__construct($message);
    }
}
