<?php

namespace TTU\Charon\Exceptions;

/**
 * Class NotFoundException.
 *
 * Thrown when the accessed resource cannot be found or there is some
 * other similar problem.
 *
 * This should be extended into more specific exceptions
 * i.e. CharonNotFoundException.
 *
 * @package TTU\Charon\Exceptions
 */
class NotFoundException extends CharonException
{
    protected $status = 404;

    /**
     * NotFoundException constructor.
     */
    public function __construct()
    {
        $message = $this->build(func_get_args());

        return parent::__construct($message);
    }
}
