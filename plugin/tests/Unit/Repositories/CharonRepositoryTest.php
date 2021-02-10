<?php

namespace Tests\Unit\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery\Mock;
use TTU\Charon\Models\Charon;
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
    /** @var Mock|LabRepository */
    private $labRepository;

    /** @var CharonRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->repository = new CharonRepository(
            Mockery::mock(ModuleService::class),
            Mockery::mock(FileUploadService::class),
            Mockery::mock(GradebookService::class),
            Mockery::mock(CharonDefenseLabRepository::class),
            $this->labRepository = Mockery::mock(LabRepository::class)
        );
    }

    public function testSaveCharonUpdatesLabs()
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

        $updated = [
            'docker_test_root' => '/test/root',
            'group_size' => 5,
            'defense_labs' => [['id' => 1], ['id' => 2]],
            'system_extra' => null,
        ];

        $this->labRepository->shouldReceive('getLabsIdsByCharonId')
            ->with(3)
            ->once()
            ->andReturn([1, 3]);

        $this->labRepository->shouldReceive('deleteLab')
            ->with(3, 3)
            ->once();

        $this->labRepository->shouldReceive('makeLab')
            ->with(3, 2)
            ->once();

        $modifiableFields = [
            'name', 'project_folder', 'defense_duration', 'defense_threshold', 'docker_timeout', 'docker_content_root',
            'docker_test_root', 'group_size', 'tester_extra', 'system_extra', 'tester_type_code', 'choose_teacher'
        ];

        $this->repository->saveCharon($charon, $updated, $modifiableFields);
    }

    public function testSaveCharonSkipsLabsWhenNoKey()
    {
        Event::fake();

        $charon = Mockery::spy(Charon::class);
        $charon->shouldReceive('setAttribute')->with('group_size', '5');
        $charon->shouldReceive('getAttribute')->with('id')->andReturn(3);
        $charon->shouldReceive('save');

        $updated = ['group_size' => 5];

        $this->labRepository->shouldReceive('getLabsIdsByCharonId')->never();

        $this->labRepository->shouldReceive('deleteLab')->never();

        $this->labRepository->shouldReceive('makeLab')->never();

        $modifiableFields = [
            'name', 'project_folder', 'defense_duration', 'defense_threshold', 'docker_timeout', 'docker_content_root',
            'docker_test_root', 'group_size', 'tester_extra', 'system_extra', 'tester_type_code', 'choose_teacher'
        ];

        $this->repository->saveCharon($charon, $updated, $modifiableFields);
    }
}
