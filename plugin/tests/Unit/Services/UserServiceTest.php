<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Zeizig\Moodle\Services\UserService;

class UserServiceTest extends TestCase
{

    /** @var UserService  */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    public function testGetUniidIfTaltechUsername()
    {
        $uniid = $this->service->getUniidIfTaltechUsername('user@ttu.ee');
        $uniid2 = $this->service->getUniidIfTaltechUsername('user@taltech.ee');
        $notUniid = $this->service->getUniidIfTaltechUsername('user@mail.ee');

        $this->assertEquals('user', $uniid);
        $this->assertEquals('user', $uniid2);
        $this->assertEquals('user@mail.ee', $notUniid);
    }
}
