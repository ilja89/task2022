<?php

namespace Tests\Traits;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GradingMethod;
use \Mockery as m;

trait MocksCharon
{
    protected function getNewPreferBestCharonMock()
    {
        $gradingMethod = m::mock(GradingMethod::class, ['isPreferBest' => true]);
        $charon = new Charon;
        $charon->gradingMethod = $gradingMethod;
        return $charon;
    }

    protected function getNewPreferLastCharonMock()
    {
        $gradingMethod = m::mock(GradingMethod::class, ['isPreferBest' => false]);
        $charon = new Charon;
        $charon->gradingMethod = $gradingMethod;
        return $charon;
    }

    protected function getCharon($props = [], $methods = [])
    {
        if ($methods === []) {
            $charon = m::mock(Charon::class)->makePartial();
        } else {
            $charon = m::mock(Charon::class, $methods)->makePartial();
        }

        foreach ($props as $propName => $propValue) {
            $charon->$propName = $propValue;
        }

        return $charon;
    }
}
