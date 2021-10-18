<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Tests\TestCase;
use TTU\Charon\Repositories\TemplatesRepository;

class LabRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new LabRepository();
    }

    public function testCheckTemplateRepositoryWorksFineTestDifferentCases()
    {
//        /** @var Charon $charon */
//        $charon65 = factory(Charon::class)->create(['id' => 65]);
//
//        /** @var Template $template1 */
//        $template1 = factory(Template::class)->create(['charon_id' => $charon65->id, 'path' => 'EX65/Model/Cat.java', 'contents' => 'code', 'created_at' => Carbon::now()]);
//
//
//        $actual = $this->repository->getLabsWithStartAndEndTimes($charon65->id);
//
//        $this->assertEquals(4, count($actual));
//
//        foreach ($actual as $result){
//            $this->assertEquals(65, $result->charon_id);
//            $this->assertEquals('code', $result->contents);
//        }
    }
}
