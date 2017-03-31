<?php

/*------------------------*\
   General
\*------------------------*/
$string['pluginname']           = 'Charon';
$string['modulename']           = 'Charon';
$string['plugin_settings']      = 'Charoni seaded';
$string['pluginadministration'] = 'Charoni administreerimine';
$string['modulenameplural']     = 'Charonid';
$string['capella:addinstance']  = 'Lisa uus charon';

/*------------------------*\
   Course settings form
\*------------------------*/
$string['tester_settings']      = 'Testeri seaded';
$string['presets']              = 'Presetid';
$string['unittests_git']        = 'Testide Git';
$string['edit_preset']          = 'Muuda preseti';
$string['preset_name']          = 'Preseti nimi';
$string['grade_name_prefix']    = 'Hinde nime eesliide';
$string['grade_name_postfix']   = 'Hinde nime lõpp';
$string['id_number_postfix']    = 'ID numbri lõpp';
$string['save_preset']          = 'Salvesta preset';
$string['update_preset']        = 'Uuenda preseti';

$string['unittests_git_helper'] = 'The Git URL in which there are tests for this course. Tests for each assignment '
                                  . 'should be in the "Project" directory specified in the new Charon form.';
$string['tester_type_helper']   = 'The tester type this course\'s assignments use. Usually the programming language '
                                  . 'mainly used in the course. Can also be overridden when creating a task.';
$string['preset_name_helper']   = 'Name of the preset. Choose something easy to understand, eg. home tasks.';
$string['extra_cs_helper'] = 'Extra parameters sent to the tester. Can be overridden while creating a '
                             . 'task.';
$string['max_points_cs_helper'] = 'Maximum points gotten from these types of assignments.';
$string['grading_method_cs_helper'] = 'Grading method used in these assignments.';
$string['grades_cs_helper'] = 'Grades checked in tasks with this preset. Tests_X is for automated tests, Style_X '
                              . 'is for stylechecks (eg. checkstyle), Custom_X can be used in any way but must be '
                              . 'graded manually (useful for defence).';
$string['grade_name_cs_helper'] = 'Moodle grade name. This is shown to students. Can use the project folder name '
                                  . 'or task name as the prefix. Eg. EX01 - Tests where "EX01" is the project folder '
                                  . 'and " - Tests" is the postfix.';
$string['max_points_grade_cs_helper'] = 'Max points possible for grades created with this preset. Eg. every Tests_1 '
                                        . 'can have the max points of 10.';
$string['id_number_postfix_helper'] = 'ID number is a unique identifier used in calculating total points. Will use '
                                      . 'the project folder as its prefix. Eg. "EX01_tests" where "_tests" is the '
                                      . 'value specified here.';
$string['calculation_formula_cs_helper'] = 'Formula for calculating the resulting grade. Use the usual Moodle '
                                           . 'formula syntax but instead of grade item ids specify which grade type '
                                           . 'is used. Eg. "=sum([[Tests_1]], [[Tests_2]]) * [[Style_1]] * [[Custom_1]]".';

/*------------------------*\
   Instance form
\*------------------------*/
$string['naming']              = 'Nimetamine';
$string['task_info']           = 'Ülesande andmed';
$string['task_name']           = 'Ülesande nimi';
$string['project_folder_name'] = 'Projekti kausta nimi';
$string['extra']               = 'Ekstra parameetrid';
$string['tester_type']         = 'Testeri tüüp';
$string['grading']             = 'Hindamine';
$string['grading_method']      = 'Hindamise meetod';
$string['grademaps']           = 'Hinded';
$string['grades']              = 'Hinded';
$string['grade_name']          = 'Hinde nimi';
$string['max_points']          = 'Maksimum punktid';
$string['id_number']           = 'ID number';
$string['calculation_formula'] = 'Arvutuse valem';
$string['preset']              = 'Preset';

/*------------------------*\
   Assignment view
\*------------------------*/
$string['submission']     = 'Esitus';
$string['submissions']    = 'Esitused';
$string['commit_message'] = 'Commiti kommentaar';
$string['files']          = 'Failid';
$string['deadlines']      = 'Tähtajad';
$string['after']          = 'Pärast';
$string['percentage']     = 'Protsent';
