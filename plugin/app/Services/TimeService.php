<?php

namespace TTU\Charon\Services;

class TimeService
{
    public function formatDateObjectToTimestamp(Array $dateObject)
    {
        foreach ($dateObject as $key => $date)
        {
            $dateObject->$key = strtotime($dateObject->$key);
        }
        return $dateObject;
    }
}