<?php

namespace TTU\Charon\Repositories;

use TTU\Charon\Models\UnitTest;

class UnitTestRepository
{
    /**
     * @param array $fields
     * @return UnitTest
     */
    public function create($fields = [])
    {
        return UnitTest::create($fields);
    }
}
