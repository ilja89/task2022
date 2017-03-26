<?php

namespace TTU\Charon\Traits;

/**
 * Class HasGradeType.
 *
 * @property int grade_type_code
 */
trait HasGradeType
{
    public function getGradeTypeName()
    {
        if ($this->grade_type_code <= 100) {
            $gradeTypeName = 'Tests_' . $this->grade_type_code;
        } else if ($this->grade_type_code <= 1000) {
            $gradeTypeName = 'Style_' . $this->grade_type_code % 100;
        } else {
            $gradeTypeName = 'Custom_' . $this->grade_type_code % 1000;
        }

        return $gradeTypeName;
    }

    public function isTestsGrade()
    {
        return $this->grade_type_code < 100;
    }
}
