<?php

namespace mod_charon\grades;

use \core_grades\local\gradeitem\itemnumber_mapping;

class gradeitems implements itemnumber_mapping
{
    public static function get_itemname_mapping_for_component(): array
    {
        $answer = [];

        for ($i = 0; $i <= 100; $i++) {
            $answer[$i] = 'tests';
        }

        for ($i = 0; $i <= 10; $i++) {
            $answer[100 + $i] = 'style';
        }

        for ($i = 0; $i <= 10; $i++) {
            $answer[1000 + $i] = 'defence';
        }

        return $answer;
    }
}