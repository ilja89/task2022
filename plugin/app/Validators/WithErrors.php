<?php

namespace TTU\Charon\Validators;

use Illuminate\Validation\Validator;

abstract class WithErrors extends Validator
{
    /**
     * @param string $field
     * @param string $message
     * @param mixed ...$params
     */
    protected function addError(string $field, string $message, ...$params)
    {
        $this->errors()->add($field, sprintf($message, ...$params));
    }
}
