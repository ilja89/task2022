<?php

namespace Tests\Integration\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Models\CharonDefenseLab;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use Tests\TestCase;

class CharonDefenseLabRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var CharonDefenseLabRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CharonDefenseLabRepository();
    }

    public function testGetLabByDefenseLabIdReturnsLabIfDefenseLabPresent()
    {
        $lab = Lab::create(['start' => Carbon::now(), 'end' => Carbon::now(), 'course_id' => 0]);
        $defenseLab = CharonDefenseLab::create(['lab_id' => $lab->id, 'charon_id' => 0]);

        $actual = $this->repository->getLabByDefenseLabId($defenseLab->id);

        $this->assertEquals($lab->id, $actual->id);
    }

    public function testGetLabByDefenseLabIdThrowsIfDefenseLabMissing()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getLabByDefenseLabId(9999999999);
    }
}
