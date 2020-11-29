<?php

namespace TTU\Charon\Services;

use TTU\Charon\Repositories\TestSuiteRepository;
use TTU\Charon\Repositories\UnitTestRepository;

/**
 * Class TestSuiteService.
 *
 * @package TTU\Charon\Services
 */
class TestSuiteService
{
    /** @var RequestHandlingService */
    private $requestHandlingService;

    /** @var TestSuiteRepository */
    private $testSuiteRepository;

    /** @var UnitTestRepository */
    private $unitTestRepository;

    /**
     * @param RequestHandlingService $requestHandlingService
     * @param TestSuiteRepository $testSuiteRepository
     * @param UnitTestRepository $unitTestRepository
     */
    public function __construct(
        RequestHandlingService $requestHandlingService,
        TestSuiteRepository $testSuiteRepository,
        UnitTestRepository $unitTestRepository
    ) {
        $this->requestHandlingService = $requestHandlingService;
        $this->testSuiteRepository = $testSuiteRepository;
        $this->unitTestRepository = $unitTestRepository;
    }

    /**
     * Save submissions and results from given results request (arete v2).
     *
     * @param array $testSuites
     * @param int $submissionId
     */
    public function saveSuites(array $testSuites, int $submissionId) {
        $gradeCode = 1;

        foreach ($testSuites as $testSuite) {
            $createdTestSuite = $this->testSuiteRepository->create([
                'submission_id' => $submissionId,
                'name' => $testSuite['name'],
                'file' => $testSuite['file'],
                'weight' => $testSuite['weight'] == null ? 1 : $testSuite['weight'],
                'passed_count' => $testSuite['passedCount'],
                'grade' => $testSuite['grade']
            ]);

            $result = $this->requestHandlingService->getResultFromRequest($submissionId, $testSuite, $gradeCode++);
            $result->save();

            foreach ($testSuite['unitTests'] as $unitTest) {
                $this->unitTestRepository->create([
                    'test_suite_id' => $createdTestSuite->id,
                    'groups_depended_upon' => $this->handleMaybeLists($unitTest['groupsDependedUpon']),
                    'status' => $unitTest['status'],
                    'weight' => $unitTest['weight'] == null ? 1 : $unitTest['weight'],
                    'print_exception_message' => $unitTest['printExceptionMessage'],
                    'print_stack_trace' => $unitTest['printStackTrace'],
                    'time_elapsed' => $unitTest['timeElapsed'],
                    'methods_depended_upon' => $this->handleMaybeLists($unitTest['methodsDependedUpon']),
                    'stack_trace' => $this->constructStackTrace($unitTest['stackTrace']),
                    'name' => $unitTest['name'],
                    'stdout' => $this->handleMaybeLists($unitTest['stdout']),
                    'exception_class' => $unitTest['exceptionClass'],
                    'exception_message' => $unitTest['exceptionMessage'],
                    'stderr' => $this->handleMaybeLists($unitTest['stderr'])
                ]);
            }
        }
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
     * @param $list
     * @return string
     */
    private function handleMaybeLists($list)
    {
        if (is_array($list)) {
            return implode(', ', $list);
        }
        return '';
    }
}
