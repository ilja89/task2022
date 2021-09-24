<?php

namespace TTU\Charon\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimmer;

class TrimStrings extends BaseTrimmer
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'templateContents'
    ];

    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        foreach ($this->except as $pattern) {
            if (preg_match("/" . $pattern . "/", $key)) {
                return $value;
            }
        }

        return is_string($value) ? trim($value) : $value;
    }
}
