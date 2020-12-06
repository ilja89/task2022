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
use TTU\Charon\Repositories\LabRepository;
use Zeizig\Moodle\Services\FileUploadService;
use Zeizig\Moodle\Services\GradebookService;
use Zeizig\Moodle\Services\ModuleService;

class CharonRepositoryTest extends TestCase
{

    public function testSaveCharon()
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
        $charon->shouldReceive('getAttribute')->with('id')->andReturn(3);
        $charon->shouldReceive('save');

        $labRepository = Mockery::mock(LabRepository::class);

        $updated = [
            'docker_test_root' => '/test/root',
            'group_size' => 5,
            'defense_labs' => [['id' => 1], ['id' => 2]],
            'system_extra' => null,
        ];

        $labRepository->shouldReceive('getLabsIdsByCharonId')
            ->with(3)
            ->once()
            ->andReturn([1, 3]);

        $labRepository->shouldReceive('deleteLab')
            ->with(3, 3)
            ->once();

        $labRepository->shouldReceive('makeLab')
            ->with(3, 2)
            ->once();

        $repository = new CharonRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(FileUploadService::class),
            Mockery::mock(GradebookService::class),
            Mockery::mock(CharonDefenseLabRepository::class),
            $labRepository
        );

        $modifiableFields = [
            'name', 'project_folder', 'defense_duration', 'defense_threshold', 'docker_timeout', 'docker_content_root',
            'docker_test_root', 'group_size', 'tester_extra', 'system_extra', 'tester_type_code', 'choose_teacher'
        ];

        $repository->saveCharon($charon, $updated, $modifiableFields);
    }
}
