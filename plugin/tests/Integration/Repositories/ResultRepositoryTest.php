<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\ResultRepository;

class ResultRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var ResultRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new ResultRepository();
    }

    public function testSaveIfGrademapPresentIgnoresWhenMissingFields()
    {
        $this->repository->saveIfGrademapPresent(['stdout' => 'should not find this']);

        $actual = Result::where('stdout', 'should not find this')->first();

        $this->assertNull($actual);
    }

    public function testSaveIfGrademapPresentSavesIfGrademapFound()
    {
        /** @var Charon $charon */
        $charon = factory(Charon::class)->create(['course' => 0, 'category_id' => 0]);

        /** @var Submission $submission */
        $submission = factory(Submission::class)->create(['charon_id' => $charon->id]);

        Grademap::create(['charon_id' => $charon->id, 'grade_type_code' => 987654321]);

        $this->repository->saveIfGrademapPresent([
            'submission_id' => $submission->id,
            'percentage' => 1.0,
            'calculated_result' => '15',
            'stdout' => 'all is fine',
            'grade_type_code' => 987654321
        ]);

        /** @var Result $actual */
        $actual = Result::where('grade_type_code', 987654321)->first();

        $this->assertNotNull($actual);
        $this->assertEquals('all is fine', $actual->stdout);
    }
}
