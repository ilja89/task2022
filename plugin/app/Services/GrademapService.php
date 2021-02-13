<?php

namespace TTU\Charon\Services;

use Illuminate\Support\Facades\Log;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class GrademapService.
 * Handles business logic stuff related to Grademaps.
 */
class GrademapService
{
    /** @var GradebookService */
    protected $gradebookService;

    /**
     * GrademapService constructor.
     *
     * @param GradebookService $gradebookService
     */
    public function __construct(GradebookService $gradebookService)
    {
        $this->gradebookService = $gradebookService;
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
}
