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
        return [
            'slug'   => $this->slug,
            'status' => $this->status,
            'title'  => $this->title,
            'detail' => $this->detail,
        ];
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
