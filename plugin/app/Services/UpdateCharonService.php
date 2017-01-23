<?php

namespace TTU\Charon\Services;

use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class UpdateCharonService.
 *
 * @package TTU\Charon\Services
 */
class UpdateCharonService
{
    /** @var GrademapService */
    protected $grademapService;

    /** @var GradebookService */
    protected $gradebookService;

    /**
     * UpdateCharonService constructor.
     *
     * @param  GrademapService  $grademapService
     * @param  GradebookService  $gradebookService
     */
    public function __construct(GrademapService $grademapService, GradebookService $gradebookService)
    {
        $this->grademapService = $grademapService;
        $this->gradebookService = $gradebookService;
    }

    /**
     * Update Grademaps with info from the request.
     *
     * @param  Request  $request
     * @param  Charon  $charon
     *
     * @return void
     */
    public function updateGrademaps(Request $request, Charon $charon)
    {
        $grademaps = $charon->grademaps;
        $newGrademaps = $request->grademaps;

        // Check previous Grademaps.
        foreach ($grademaps as $grademap) {
            $newGrademap = $this->getGrademapByGradeType($newGrademaps, $grademap->grade_type_code);

            $newGrademaps[$grademap->grade_type_code]['checked'] = true;
            $this->updateExistingGrademap($grademap, $newGrademap);
        }

        // Check the rest of the Grademaps.
        foreach ($newGrademaps as $gradeType => $newGrademap) {
            if (!$newGrademap['checked']) {
                $this->grademapService->createGrademapWithGradeItem($charon, $gradeType, $request->course, $newGrademap);
            }
        }
    }

    /**
     * Given an old Grademap and an array for the new Grademap will update
     * the old Grademap with new info or delete the Grademap if there is no new
     * Grademap.
     *
     * New grademap fields: grademap_name, max_points, id_number.
     *
     * @param  Grademap  $grademap
     * @param  array  $newGrademap
     *
     * @return Grademap
     */
    private function updateExistingGrademap($grademap, $newGrademap)
    {
        if ($newGrademap === null) {
            $this->grademapService->deleteGrademap($grademap);
            return null;
        }

        $grademap->name = $newGrademap['grademap_name'];
        $this->gradebookService->updateGradeItem($grademap->grade_item_id, [
            'itemname' => $newGrademap['grademap_name'],
            'grademax' => $newGrademap['max_points'],
            'idnumber' => $newGrademap['id_number']
        ]);

        return $grademap;
    }

    /**
     * Gets the Grademap with the given Grade type from given grademaps.
     * The grademaps come from the request so it's a map where grade type => grademap info.
     *
     * @param  array  $grademaps
     * @param  integer  $gradeTypeCode
     *
     * @return array
     */
    private function getGrademapByGradeType($grademaps, $gradeTypeCode)
    {
        foreach ($grademaps as $gradeType => $grademap) {
            if ($gradeType == $gradeTypeCode) {
                return $grademap;
            }
        }

        return null;
    }
}
