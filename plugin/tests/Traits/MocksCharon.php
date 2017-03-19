<?php

namespace Tests\Traits;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GradingMethod;
use \Mockery as m;

trait MocksCharon
{
    private function getNewPreferBestCharonMock()
    {
        $gradingMethod = m::mock(GradingMethod::class, ['isPreferBest' => true]);
        $charon = new Charon;
        $charon->gradingMethod = $gradingMethod;
        return $charon;
    }

    private function getNewPreferLastCharonMock()
    {
        $gradingMethod = m::mock(GradingMethod::class, ['isPreferBest' => false]);
        $charon = new Charon;
        $charon->gradingMethod = $gradingMethod;
        return $charon;
    }
}
