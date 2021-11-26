<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Repositories\GradeItemRepository;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class GrademapService.
 * Handles business logic stuff related to Grademaps.
 */
class GrademapService
{
    /** @var GradebookService */
    protected $gradebookService;

    /** @var GradeItemRepository */
    private $gradeItemRepository;

    /**
     * GrademapService constructor.
     *
     * @param GradebookService $gradebookService
     * @param GradeItemRepository $gradeItemRepository
     */
    public function __construct(GradebookService $gradebookService, GradeItemRepository $gradeItemRepository)
    {
        $this->gradebookService = $gradebookService;
        $this->gradeItemRepository = $gradeItemRepository;
    }

    /**
     * Links the now-existing Grade Items with given Charon's Grademaps.
     * This is called after the Grade Items have been created, by observing the
     * course_module_created event.
     *
     * @param Charon $charon
     */
    public function linkGrademapsAndGradeItems(Charon $charon)
    {
        $grademaps = $charon->grademaps;
        $gradeItems = $charon->gradeItems();

        foreach ($grademaps as $grademap) {
            foreach ($gradeItems as $gradeItem) {
                if ($gradeItem->itemnumber === $grademap->grade_type_code) {
                    $grademap->grade_item_id = $gradeItem->id;
                    $grademap->save();
                }
            }
        }
    }

    /**
     * Deletes the given Grademap.
     *
     * @param Grademap $grademap
     *
     * @return void
     */
    public function deleteGrademap(Grademap $grademap)
    {
        // TODO: Move to repository
        $grademap->gradeItem->delete();
        $grademap->delete();
    }

    /**
     * @param Charon $charon
     * @param integer $gradeTypeCode
     * @param integer $courseId
     * @param array $requestGradeMap
     */
    public function createGrademapWithGradeItem($charon, $gradeTypeCode, $courseId, $requestGradeMap)
    {
        Log::info("Creating a grade map: ", [$requestGradeMap]);

        $this->gradebookService->addGradeItem(
            $courseId,
            $charon->id,
            $gradeTypeCode,
            $requestGradeMap['grademap_name'],
            $requestGradeMap['max_points'],
            $requestGradeMap['id_number']
        );

        // We cannot add Grade Item ID here because it is not yet in the database (Moodle is great!)
        // Instead we can use event listeners (db/events.php) and wait for them to be added.
        $charon->grademaps()->save(new Grademap([
            'grade_type_code' => $gradeTypeCode,
            'name' => $requestGradeMap['grademap_name'],
            'grade_item_id' => 0,
            'persistent' => $gradeTypeCode > 1000 && isset($requestGradeMap['persistent']) && (bool) $requestGradeMap['persistent']
        ]));
    }

    /**
     * @param string $calculationFormula
     * @param Result[]|Collection $results
     * @param int $studentId
     *
     * @return array
     */
    public function findFormulaParams(string $calculationFormula, $results, int $studentId): array
    {
        $params = [];
        foreach ($results as $result) {
            if ($result->user_id == $studentId) {

                $grademap = $result->getGrademap();
                // TODO: expect results that are not included in calculation formula?
                if ($grademap === null || $grademap->gradeItem === null) {
                    continue;
                }

                $params['gi' . $grademap->gradeItem->id] = $result->calculated_result;
            }
        }

        $gradeIds = [];
        preg_match_all('/##gi(\d+)##/', $calculationFormula, $gradeIds);

        if (!isset($gradeIds[1]) || count($gradeIds[1]) <= count($params)) {
            return $params;
        }

        foreach ($gradeIds[1] as $id) {
            if (isset($params["gi" . $id])) {
                continue;
            }

            $gradeItem = $this->gradeItemRepository->find(intval($id));
            if ($gradeItem == null) {
                continue;
            }

            /** @var GradeGrade $grade */
            $grade = $gradeItem->gradesForUser($studentId);
            if ($grade == null) {
                continue;
            }

            $params['gi' . $gradeItem->id] = intval(isset($grade->finalgrade) ? $grade->finalgrade : $grade->rawgrade);
        }

        return $params;
    }
}
