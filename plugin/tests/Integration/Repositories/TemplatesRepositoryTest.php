<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Template;
use TTU\Charon\Repositories\LabTeacherRepository;
use Tests\TestCase;
use TTU\Charon\Repositories\TemplatesRepository;

class TemplatesRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var LabTeacherRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = new TemplatesRepository();
    }

    public function testCheckTemplateRepositoryWorksFineTestDifferentCases()
    {
        /** @var Charon $charon */
        $charon55 = factory(Charon::class)->create(['id' => 55, 'category_id' => 0]);

        /** @var Charon $charon */
        $charon65 = factory(Charon::class)->create(['id' => 65, 'category_id' => 0]);

        /** @var Template $template1 */
        $template1 = factory(Template::class)->create(['charon_id' => $charon65->id, 'path' => 'EX65/Model/Cat.java', 'contents' => 'code']);

        /** @var Template $template2 */
        $template2 = factory(Template::class)->create(['charon_id' => $charon65->id, 'path' => 'EX65/Model/Dog.java', 'contents' => 'code']);

        /** @var Template $template3 */
        $template3 = factory(Template::class)->create(['charon_id' => $charon55->id, 'path' => 'EX55/Home.java', 'contents' => 'code']);

        /** @var Template $template4 */
        $template4 = factory(Template::class)->create(['charon_id' => $charon65->id, 'path' => 'EX65/Life.java', 'contents' => 'code']);

        /** @var Template $template5 */
        $template5 = factory(Template::class)->create(['charon_id' => $charon65->id, 'path' => 'EX65/Home.java', 'contents' => 'code']);

        $actual = $this->repository->getTemplates($charon65->id);

        $this->assertEquals(4, count($actual));

        foreach ($actual as $result){
            $this->assertEquals(65, $result->charon_id);
            $this->assertEquals('code', $result->contents);
        }

        $actual = $this->repository->getTemplates($charon55->id);

        $this->assertEquals(1, count($actual));

        $template1->contents = '';

        $actual = $this->repository->updateTemplateContents($template1);

        $this->assertEquals('', $actual['contents']);

        $actual = $this->repository->getTemplates($charon65->id);

        $this->assertEquals(4, count($actual));

        foreach ($actual as $result){
            if ($result->id == $template1->id){
                $this->assertEquals('', $result->contents);
            } else {
                $this->assertEquals('code', $result->contents);
            }
        }

        $actual = $this->repository->deleteTemplate($charon65->id, $template4->path);

        $this->assertEquals(true, $actual);

        $actual = $this->repository->deleteTemplate($charon55->id, $template5->path);

        $this->assertEquals(false, $actual);

        $actual = $this->repository->getTemplates($charon65->id);

        $this->assertEquals(3, count($actual));

        $actual = $this->repository->getTemplates($charon55->id);

        $this->assertEquals(1, count($actual));

        $template2->path = '';

        $actual = $this->repository->updateTemplateContents($template2);

        $this->assertEquals(false, $actual);
    }
}
