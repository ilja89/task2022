<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Preset;
use TTU\Charon\Models\PresetGrade;
use Zeizig\Moodle\Models\Course;

/**
 * Class PresetsController.
 *
 * @package TTU\Charon\Http\Controllers\Api
 */
class PresetsController extends Controller
{
    /**
     * Save the preset from the request.
     *
     * @param  Course $course
     *
     * @return Preset
     */
    public function store(Course $course)
    {
        $preset = $this->savePresetFromRequest($course->id);
        $preset->load('presetGrades');

        return $preset;
    }

    /**
     * @param Course $course
     * @param Preset $preset
     *
     * @return Preset
     */
    public function update(Course $course, Preset $preset)
    {
        $preset = $this->getPresetFromRequest($course->id, $preset);
        $preset->load('presetGrades');
        $preset->save();
        PresetGrade::where('preset_id', $preset->id)
            ->delete();
        $preset->presetGrades()->saveMany($this->getPresetGradesFromRequest());

        return $preset;
    }

    /**
     * Saves the preset from the request.
     *
     * @param  int  $courseId
     *
     * @return Preset
     */
    private function savePresetFromRequest($courseId)
    {
        $preset = $this->getPresetFromRequest($courseId);
        $preset->save();
        $preset->presetGrades()->saveMany($this->getPresetGradesFromRequest());

        return $preset;
    }

    private function getPresetFromRequest($courseId, $preset = null)
    {
        if ($preset === null) {
            $preset = new Preset;
        }

        $preset->course_id = $courseId;
        $preset->name = $this->request['preset']['name'];
        $preset->parent_category_id = $this->request['preset']['parent_category_id'];
        $preset->calculation_formula = $this->request['preset']['calculation_formula'];
        $preset->tester_extra = $this->request['preset']['tester_extra'];
        $preset->system_extra = $this->request['preset']['system_extra'];
        $preset->max_result = $this->request['preset']['max_result'];
        $preset->grading_method_code = $this->request['preset']['grading_method_code'];
        //$preset->grouping_id = $this->request['preset']['grouping_id'];

        return $preset;
    }

    private function getPresetGradesFromRequest()
    {
        $grades = new Collection();
        foreach ($this->request['preset']['preset_grades'] as $grade) {
            $grades->add(new PresetGrade([
                'grade_name_prefix_code' => $grade['grade_name_prefix_code'],
                'grade_type_code' => $grade['grade_type_code'],
                'grade_name' => $grade['grade_name'],
                'max_result' => $grade['max_result'],
                'id_number_postfix' => $grade['id_number_postfix'],
            ]));
        }
        return $grades;
    }
}
