<?php

namespace TTU\Charon\Traits;

use TTU\Charon\Constants\GradeType;

/**
 * Class HasGradeType.
 *
 * @property int grade_type_code
 */
trait HasGradeType
{
    public function getGradeTypeName(): string
    {
        if ($this->grade_type_code < GradeType::STYLE_TYPE_MINIMUM) {
            return 'Tests_' . $this->grade_type_code;
        } else if ($this->grade_type_code < GradeType::CUSTOM_TYPE_MINIMUM) {
            return 'Style_' . $this->grade_type_code % (GradeType::STYLE_TYPE_MINIMUM - 1);
        }
        return 'Custom_' . $this->grade_type_code % (GradeType::CUSTOM_TYPE_MINIMUM - 1);
    }

    public function isTestsGrade(): bool
    {
        return $this->grade_type_code < GradeType::STYLE_TYPE_MINIMUM;
    }

    public function isStyleGrade(): bool
    {
        return $this->grade_type_code < GradeType::CUSTOM_TYPE_MINIMUM && $this->grade_type_code >= GradeType::STYLE_TYPE_MINIMUM;
    }

    public function isCustomGrade(): bool
    {
        return $this->grade_type_code >= GradeType::CUSTOM_TYPE_MINIMUM;
    }
}
