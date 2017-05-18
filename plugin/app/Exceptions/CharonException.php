<?php

namespace TTU\Charon\Exceptions;

/**
 * Class CharonException.
 * Base Exception class that application specific exceptions will extend.
 *
 * @package TTU\Charon\Exceptions
 */
abstract class CharonException extends \Exception
{
    /**
     * Slug for this exception. Used to find the error in config/errors.php
     * @var string
     */
    protected $slug;

    /** @var int */
    protected $status;

    /** @var string */
    protected $title;

    /** @var string */
    protected $detail;

    /**
     * CharonException constructor.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }

    /**
     * Return the array representation of this exception.
     * Useful for returning JSON.
     *
     * @return array
     */
    public function toArray()
    {
        $array = get_object_vars($this);
        // Remove unneeded variables, xdebug messages etc.
        unset($array['message'], $array['code'], $array['xdebug_message'], $array['file'], $array['line']);

        return $array;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Build the Exception
     *
     * @param array $args
     * @return string
     */
    protected function build(array $args)
    {
        $this->slug = array_shift($args);

        $error = config('errors.' . $this->slug);

        $this->title  = $error['title'];
        $this->detail = vsprintf($error['detail'], $args);

        return $this->detail;
    }
}
