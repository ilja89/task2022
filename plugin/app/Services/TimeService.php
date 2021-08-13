<?php

namespace TTU\Charon\Services;

class TimeService
{
    /**
     * @param $dateList
     * @return mixed
     */
    public function formatDateObjectToTimestamp($dateList)
    {
        foreach ($dateList as &$date)
        {
            $date = strtotime($date);
        }
        return $dateList;
    }
}