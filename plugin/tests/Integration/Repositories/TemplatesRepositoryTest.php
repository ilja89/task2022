<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
use Tests\TestCase;
use TTU\Charon\Repositories\TemplatesRepository;

class TemplatesRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var TemplatesRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TemplatesRepository();
    }

    public function testCheckTemplateRepositoryWorksFineTestDifferentCases()
    {
        /** @var Charon $charon1 */
        $charon1 = factory(Charon::class)->create(['category_id' => 0]);

        /** @var Charon $charon2 */
        $charon2 = factory(Charon::class)->create(['category_id' => 0]);

        /** @var Template $template1 */
        $template1 = factory(Template::class)->create(['charon_id' => $charon2->id, 'path' => 'EX65/Model/Cat.java', 'contents' => 'code', 'created_at' => Carbon::now()]);

        /** @var Template $template2 */
        $template2 = factory(Template::class)->create(['charon_id' => $charon2->id, 'path' => 'EX65/Model/Dog.java', 'contents' => 'code', 'created_at' => Carbon::now()]);

        /** @var Template $template3 */
        $template3 = factory(Template::class)->create(['charon_id' => $charon1->id, 'path' => 'EX55/Home.java', 'contents' => 'code', 'created_at' => Carbon::now()]);

        /** @var Template $template4 */
        $template4 = factory(Template::class)->create(['charon_id' => $charon2->id, 'path' => 'EX65/Life.java', 'contents' => 'code', 'created_at' => Carbon::now()]);

        /** @var Template $template5 */
        $template5 = factory(Template::class)->create(['charon_id' => $charon2->id, 'path' => 'EX65/Home.java', 'contents' => 'code', 'created_at' => Carbon::now()]);

        $actual = $this->repository->getTemplates($charon2->id);

        $this->assertEquals(4, count($actual));

        foreach ($actual as $result){
            $this->assertEquals($charon2->id, $result->charon_id);
            $this->assertEquals('code', $result->contents);
        }

        $actual = $this->repository->getTemplates($charon1->id);

        $this->assertEquals(1, count($actual));

        $template6 = factory(Template::class)->create(['charon_id' => $charon2->id, 'path' => 'EX65/Home.java', 'contents' => '', 'created_at' => Carbon::now()]);

        $actual = $this->repository->getTemplates($charon2->id);

        $this->assertEquals(5, count($actual));

        foreach ($actual as $result){
            if ($result->id == $template6->id){
                $this->assertEquals('', $result->contents);
            } else {
                $this->assertEquals('code', $result->contents);
            }
        }

        $actual = $this->repository->deleteAllTemplates($charon2->id);

        $this->assertEquals(true, $actual);

        $actual = $this->repository->deleteAllTemplates($charon1->id);

        $this->assertEquals(true, $actual);

        $actual = $this->repository->deleteAllTemplates($charon1->id);

        $this->assertEquals(false, $actual);

        $actual = $this->repository->getTemplates($charon2->id);

        $this->assertEquals(0, count($actual));

        $actual = $this->repository->getTemplates($charon1->id);

        $this->assertEquals(0, count($actual));

        $template2->path = '';

        $actual = $this->repository->saveTemplate($template2->charon_id, $template2->path, $template2->contents);

        $this->assertEquals('', $actual['path']);
    }
}
