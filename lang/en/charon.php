<?php
/**
 * General
 ***************************************************************/
$string['pluginname']           = 'Charon';
$string['modulename']           = 'Charon';
$string['plugin_settings']      = 'Charon settings';
$string['pluginadministration'] = 'Charon administration';
$string['modulenameplural']     = 'Charons';
$string['capella:addinstance']  = 'Add a new Charon';

/*------------------------*\
   Course settings form
\*------------------------*/
$string['tester_settings']      = 'Tester settings';
$string['presets']              = 'Presets';
$string['unittests_git']        = 'Unittests Git';
$string['edit_preset']          = 'Edit a preset';
$string['preset_name']          = 'Preset name';
$string['grade_name_prefix']    = 'Grade name prefix';
$string['grade_name_postfix']   = 'Grade name postfix';
$string['id_number_postfix']    = 'ID number postfix';
$string['save_preset']          = 'Save preset';
$string['update_preset']        = 'Update preset';

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
$string['naming']              = 'Naming';
$string['task_info']           = 'Task info';
$string['task_name']           = 'Task name';
$string['project_folder_name'] = 'Project folder name';
$string['extra']               = 'Extra parameters';
$string['tester_type']         = 'Tester type';
$string['grading']             = 'Grading';
$string['grading_method']      = 'Grading method';
$string['grademaps']           = 'Grademaps';
$string['grades']              = 'Grades';
$string['grade_name']          = 'Grade name';
$string['max_points']          = 'Max points';
$string['id_number']           = 'ID number';
$string['calculation_formula'] = 'Calculation formula';
$string['preset']              = 'Preset';

$string['deadline'] = 'Deadline';
$string['percentage'] = 'Percentage';
$string['group'] = 'Group';

$string['task_name_helper'] = 'The name for this assignment. A category with this name will be created which will '
                            . 'contain this assignment\'s grades.';
$string['project_folder_name_helper'] = 'The folder name for this assignment. Students have to put their code in '
                                      . 'this folder. This is not shown to students so it should be included in '
                                      . 'the task description.';
$string['deadlines_helper'] = 'Deadlines for this task. If a submission is submitted after the deadline, the '
                            . 'test results will be multiplied by the percentage. Eg. deadline with 0% means '
                            . 'that after that deadline the student will receive 0 points for tests.';
$string['deadline_helper'] = 'Deadline date and time.';
$string['percentage_helper'] = 'Percentage of points multiplied if submission is after deadline.';
$string['group_helper'] = 'Group for which this deadline applies. Currently does not work.';


/*------------------------*\
   Assignment view
\*------------------------*/
$string['submission']     = 'Submission';
$string['submissions']    = 'Submissions';
$string['commit_message'] = 'Commit message';
$string['files']          = 'Files';
$string['deadlines']      = 'Deadlines';
$string['after']          = 'After';
$string['percentage']     = 'Percentage';
