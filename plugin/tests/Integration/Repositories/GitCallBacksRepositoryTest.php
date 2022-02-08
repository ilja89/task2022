<?php

namespace Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use TTU\Charon\Exceptions\IncorrectSecretTokenException;
use TTU\Charon\Models\GitCallback;
use Tests\TestCase;
use TTU\Charon\Repositories\GitCallbacksRepository;

class GitCallBacksRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GitCallbacksRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GitCallbacksRepository();
    }

    public function testFindByTokenThrowsIfTokenMissing()
    {
        $this->expectException(IncorrectSecretTokenException::class);

        $this->repository->findByToken('');
    }

    public function testFindByTokenThrowsIfIncorrectToken()
    {
        $this->expectException(IncorrectSecretTokenException::class);

        $this->repository->findByToken('123');
    }

    public function testFindByTokenSuccesful()
    {
        $gitCallback = GitCallback::create([
            'url' => 'fullurl',
            'repo' => 'repo',
            'user' => 'uniid',
            'created_at' => '00:00:00',
            'secret_token' => 'secretsecret'
        ]);

        $actual = $this->repository->findByToken('secretsecret');

        $this->assertEquals($gitCallback->id, $actual->id);
    }
}
