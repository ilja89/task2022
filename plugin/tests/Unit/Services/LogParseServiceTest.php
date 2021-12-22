<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use TTU\Charon\Services\LogParseService;
use Tests\TestCase;

class LogParseServiceTest extends TestCase
{

    const FIXTURE_PATH = __DIR__.'/../../Fixtures';

    public function testReadLogs()
    {
        $adapter = new Local(self::FIXTURE_PATH);
        $filesystem = new Filesystem($adapter);
        $contents = $filesystem->listContents('./', false);

        $storage = Storage::spy();

        Config::shouldReceive('get')
            ->with('app.log_display_lines')
            ->andReturn(500);

        $storage->shouldReceive('disk->listContents')
            ->with('./')
            ->once()
            ->andReturn($contents);

        $storage->shouldReceive('disk->path')
            ->with('laravel-2020.10.20.log')
            ->once()
            ->andReturn(self::FIXTURE_PATH . '/laravel-2020.10.20.log');

        $storage->shouldReceive('disk->path')
            ->with('laravel-2020.10.23.log')
            ->once()
            ->andReturn(self::FIXTURE_PATH . '/laravel-2020.10.23.log');

        $service = new LogParseService;

        $actual = $service->readLogs();

        $this->assertEquals(
            '[["[2020-10-23 21:04:03] develop.ERROR: Most recent line"],'
                . '["[2020-10-23 21:04:02] develop.ERROR: Error over multiple lines",'
                . '"Stack trace:",'
                . '"#0 trace line 0",'
                . '"#1 trace line 1",'
                . '"#2 trace line 2"],'
                . '["[2020-10-23 21:04:01] develop.ERROR: A single line message"],'
                . '["[2020-10-20 21:04:02] develop.ERROR: Error over multiple lines",'
                . '"Stack trace:",'
                . '"#0 trace line 0",'
                . '"#1 trace line 1"],'
                . '["[2020-10-20 21:04:01] develop.ERROR: Oldest single line"]]',
            $actual
        );
    }
}
