<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\TestSuite;

class TestSuiteRepository
{
    /**
     * @param array $fields
     * @return TestSuite
     */
    public function create($fields = [])
    {
        return TestSuite::create($fields);
    }
}
