<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\TestSuite;
use TTU\Charon\Repositories\TestSuiteRepository;

class TestSuiteRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateSavesRecord()
    {
        $repository = new TestSuiteRepository();
        $repository->create([
            'submission_id' => 0,
            'name' => 'Something very unique',
            'file' => 'filename',
            'weight' => 1,
            'passed_count' => 5,
            'grade' => 60
        ]);

        $actual = TestSuite::where('name', 'Something very unique')->first();

        $this->assertNotNull($actual);
        $this->assertEquals('Something very unique', $actual->name);
    }
}
