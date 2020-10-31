<?php

namespace Tests\Unit\Repositories;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use Tests\TestCase;
use Mockery;
use Zeizig\Moodle\Services\FileUploadService;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\ModuleService;

class CharonRepositoryTest extends TestCase
{

    public function testSaveCharonDefendingStuff()
    {
        Event::fake();

        DB::spy();
        DB::shouldReceive('table')->with('charon_defense_lab')->andReturnSelf();
        DB::shouldReceive('where')->with('charon_id', 3)->andReturnSelf();
        DB::shouldReceive('delete');

        $charon = Mockery::spy(Charon::class);
        $charon->shouldReceive('setAttribute')->with('docker_test_root', '/test/root');
        $charon->shouldReceive('setAttribute')->with('group_size', '5');
        $charon->shouldReceive('setAttribute')->with('system_extra', null);
        $charon->shouldReceive('getAttribute')->with('id')->twice()->andReturn(3);
        $charon->shouldReceive('save');

        App::spy();
        $defenseLab = Mockery::mock(CharonDefenseLab::class);
        $defenseLab->shouldReceive('save');
        App::shouldReceive('makeWith')
            ->with(CharonDefenseLab::class, ['lab_id' => 7, 'charon_id' => 3])
            ->andReturn($defenseLab);

        $updated = [
            'docker_test_root' => '/test/root',
            'group_size' => 5,
            'defense_labs' => [7],
            'system_extra' => null,
        ];

        $repository = new CharonRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(FileUploadService::class),
            Mockery::mock(GradebookService::class),
            Mockery::mock(CharonDefenseLabRepository::class),
        );

        $actual = $repository->saveCharon($charon, $updated);

        $this->assertSame($charon, $actual);
    }
}
