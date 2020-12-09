<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\TestSuite;
use TTU\Charon\Repositories\TestSuiteRepository;
use TTU\Charon\Repositories\UnitTestRepository;
use TTU\Charon\Services\RequestHandlingService;
use TTU\Charon\Models\Result;
use TTU\Charon\Services\TestSuiteService;

class TestSuiteServiceTest extends TestCase
{
    public function testSaveSuitesCreatesTestSuitesAndUnitTests()
    {
        TestSuite::unguard();

        $givenLongStacktrace = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor '
            . 'incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation'
            . ' ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit';

        $expectedLongStackTrace = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor'
            . ' incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation'
            . ' ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor i';

        $suites = [
            [
                'name' => 's1',
                'file' => 's1 file',
                'weight' => 0.5,
                'passedCount' => 5,
                'grade' => 7,
                'unitTests' => [
                    [
                        'groupsDependedUpon' => ['s1', 'u1', 'groups'],
                        'status' => 'closed',
                        'weight' => 0.6,
                        'printExceptionMessage' => 'u1 message',
                        'printStackTrace' => 'u1 stack',
                        'timeElapsed' => 150,
                        'methodsDependedUpon' => ['some', 'methods'],
                        'stackTrace' => 'short stacktrace',
                        'name' => 'u1',
                        'stdout' => ['std', 'out'],
                        'exceptionClass' => 'exception',
                        'exceptionMessage' => 'message',
                        'stderr' => ['std', 'error']
                    ],
                    [
                        'groupsDependedUpon' => '',
                        'status' => 'closed',
                        'weight' => null,
                        'printExceptionMessage' => 'u2 message',
                        'printStackTrace' => 'u2 stack',
                        'timeElapsed' => 155,
                        'methodsDependedUpon' => ['some', null, [['additional'], 'methods', null], ''],
                        'stackTrace' => $givenLongStacktrace,
                        'name' => 'u2',
                        'stdout' => 'just a string',
                        'exceptionClass' => 'exception',
                        'exceptionMessage' => 'message',
                        'stderr' => ''
                    ]
                ]
            ],
            [
                'name' => 's2',
                'file' => 's2 file',
                'weight' => null,
                'passedCount' => 11,
                'grade' => 13,
                'unitTests' => [
                    [
                        'groupsDependedUpon' => '',
                        'status' => 'closed',
                        'weight' => null,
                        'printExceptionMessage' => 'u3 message',
                        'printStackTrace' => 'u3 stack',
                        'timeElapsed' => 160,
                        'methodsDependedUpon' => '',
                        'stackTrace' => null,
                        'name' => 'u3',
                        'stdout' => null,
                        'exceptionClass' => 'exception',
                        'exceptionMessage' => 'message',
                        'stderr' => ''
                    ]
                ]
            ],
        ];

        /** @var Mock $result */
        $result = Mockery::mock(Result::class);
        $result->shouldReceive('save')->twice();

        /** @var Mock|RequestHandlingService $requestHandlingService */
        $requestHandlingService = Mockery::mock(RequestHandlingService::class);

        $requestHandlingService->shouldReceive('getResultFromRequest')
            ->with(3, $suites[0], 1)
            ->once()
            ->andReturn($result);

        $requestHandlingService->shouldReceive('getResultFromRequest')
            ->with(3, $suites[1], 2)
            ->once()
            ->andReturn($result);

        /** @var Mock|TestSuiteRepository $testSuiteRepository */
        $testSuiteRepository = Mockery::mock(TestSuiteRepository::class);

        $testSuiteRepository->shouldReceive('create')
            ->with([
                'submission_id' => 3,
                'name' => 's1',
                'file' => 's1 file',
                'weight' => 0.5,
                'passed_count' => 5,
                'grade' => 7
            ])
            ->once()
            ->andReturn(new TestSuite(['id' => 17]));

        $testSuiteRepository->shouldReceive('create')
            ->with([
                'submission_id' => 3,
                'name' => 's2',
                'file' => 's2 file',
                'weight' => 1,
                'passed_count' => 11,
                'grade' => 13
            ])
            ->once()
            ->andReturn(new TestSuite(['id' => 19]));

        /** @var Mock|UnitTestRepository $unitTestRepository */
        $unitTestRepository = Mockery::mock(UnitTestRepository::class);

        $unitTestRepository->shouldReceive('create')
            ->with([
                'test_suite_id' => 17,
                'groups_depended_upon' => 's1, u1, groups',
                'status' => 'closed',
                'weight' => 0.6,
                'print_exception_message' => 'u1 message',
                'print_stack_trace' => 'u1 stack',
                'time_elapsed' => 150,
                'methods_depended_upon' => 'some, methods',
                'stack_trace' => 'short stacktrace',
                'name' => 'u1',
                'stdout' => 'std, out',
                'exception_class' => 'exception',
                'exception_message' => 'message',
                'stderr' => 'std, error'
            ])
            ->once();

        $unitTestRepository->shouldReceive('create')
            ->with([
                'test_suite_id' => 17,
                'groups_depended_upon' => '',
                'status' => 'closed',
                'weight' => 1,
                'print_exception_message' => 'u2 message',
                'print_stack_trace' => 'u2 stack',
                'time_elapsed' => 155,
                'methods_depended_upon' => 'some, additional, methods',
                'stack_trace' => $expectedLongStackTrace,
                'name' => 'u2',
                'stdout' => 'just a string',
                'exception_class' => 'exception',
                'exception_message' => 'message',
                'stderr' => ''
            ])
            ->once();

        $unitTestRepository->shouldReceive('create')
            ->with([
                'test_suite_id' => 19,
                'groups_depended_upon' => '',
                'status' => 'closed',
                'weight' => 1,
                'print_exception_message' => 'u3 message',
                'print_stack_trace' => 'u3 stack',
                'time_elapsed' => 160,
                'methods_depended_upon' => '',
                'stack_trace' => '',
                'name' => 'u3',
                'stdout' => '',
                'exception_class' => 'exception',
                'exception_message' => 'message',
                'stderr' => ''
            ])
            ->once();

        $service = new TestSuiteService($requestHandlingService, $testSuiteRepository, $unitTestRepository);

        $service->saveSuites($suites, 3);
    }
}
