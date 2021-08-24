<?php

namespace TTU\Charon\Services;

class ConverterService
{
    /**
     * @param $object
     * @return array
     */
    public function objectToArray($object): array
    {
        $array = [];
        foreach ($object as $key => $field)
        {
            $array[$key] = $field;
        }
        return $array;
    }
}
