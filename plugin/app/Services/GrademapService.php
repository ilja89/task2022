<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Charon;

/**
 * Class GrademapService.
 * Handles business logic stuff related to Grademaps.
 */
class GrademapService
{
    /**
     * Links the now-existing Grade Items with given Charon's Grademaps.
     * This is called after the Grade Items have been created, by observing the
     * course_module_created event.
     *
     * @param  Charon  $charon
     */
    public function linkGrademapsAndGradeItems(Charon $charon)
    {
        $grademaps = $charon->grademaps;
        $gradeItems = $charon->gradeItems();

        foreach ($grademaps as $grademap) {
            foreach ($gradeItems as $gradeItem) {
                if ($gradeItem->itemnumber === $grademap->gradeType->code) {
                    $grademap->grade_item_id = $gradeItem->id;
                    $grademap->save();
                }
            }
        }
    }
}
