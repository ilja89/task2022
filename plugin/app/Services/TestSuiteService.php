<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Result;
use TTU\Charon\Repositories\TestSuiteRepository;
use TTU\Charon\Repositories\UnitTestRepository;

/**
 * Class TestSuiteService.
 *
 * @package TTU\Charon\Services
 */
class TestSuiteService
{
    /** @var AreteResponseParser */
    private $requestHandlingService;

    /** @var TestSuiteRepository */
    private $testSuiteRepository;

    /** @var UnitTestRepository */
    private $unitTestRepository;

    /**
     * @param AreteResponseParser $requestHandlingService
     * @param TestSuiteRepository $testSuiteRepository
     * @param UnitTestRepository $unitTestRepository
     */
    public function __construct(
        AreteResponseParser $requestHandlingService,
        TestSuiteRepository $testSuiteRepository,
        UnitTestRepository $unitTestRepository
    ) {
        $this->requestHandlingService = $requestHandlingService;
        $this->testSuiteRepository = $testSuiteRepository;
        $this->unitTestRepository = $unitTestRepository;
    }

    /**
     * Save submissions and retrieve results from given results request (arete v2).
     *
     * @param array $testSuites
     * @param int $submissionId
     *
     * @return Result[]
     */
    public function saveSuites(array $testSuites, int $submissionId): array
    {
        $gradeCode = 1;
        $results = [];

        foreach ($testSuites as $testSuite) {
            $createdTestSuite = $this->testSuiteRepository->create([
                'submission_id' => $submissionId,
                'name' => $testSuite['name'],
                'file' => $testSuite['file'] ?? '',
                'weight' => $testSuite['weight'] == null ? 1 : $testSuite['weight'],
                'passed_count' => $testSuite['passedCount'],
                'grade' => $testSuite['grade']
            ]);

            $results[] = $this->requestHandlingService->getResultFromRequest($submissionId, $testSuite, $gradeCode++);

            foreach ($testSuite['unitTests'] as $unitTest) {
                $this->unitTestRepository->create([
                    'test_suite_id' => $createdTestSuite->id,
                    'groups_depended_upon' => $this->toString($unitTest['groupsDependedUpon']),
                    'status' => $unitTest['status'],
                    'weight' => $unitTest['weight'] == null ? 1 : $unitTest['weight'],
                    'print_exception_message' => $unitTest['printExceptionMessage'],
                    'print_stack_trace' => $unitTest['printStackTrace'],
                    'time_elapsed' => $unitTest['timeElapsed'],
                    'methods_depended_upon' => $this->toString($unitTest['methodsDependedUpon']),
                    'stack_trace' => $this->constructStackTrace($unitTest['stackTrace']),
                    'name' => $unitTest['name'],
                    'stdout' => $this->toString($unitTest['stdout'] ?? null),
                    'exception_class' => $unitTest['exceptionClass'],
                    'exception_message' => $unitTest['exceptionMessage'],
                    'stderr' => $this->toString($unitTest['stderr'] ?? null)
                ]);
            }
        }

        return $results;
    }

    /**
     * @param $stackTrace
     * @return string
     */
    private function constructStackTrace($stackTrace)
    {
        if ($stackTrace == null) {
            return '';
        }
        return strlen($stackTrace) >= 255 ? substr($stackTrace, 0, 255) : $stackTrace;
    }

    /**
     * @param $content
     * @return string
     */
    private function toString($content)
    {
        if (is_string($content)) {
            return $content;
        }
        if (!is_array($content)) {
            return '';
        }

        $result = '';
        foreach ($content as $entry) {
            $entry = $this->toString($entry);
            if (!empty($entry)) {
                $result .= $entry . ', ';
            }
        }

        if (empty($result)) {
            return $result;
        }

        return mb_substr($result, 0, -2);
    }
}
