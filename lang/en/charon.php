<?php
/**
 * General
 ***************************************************************/
$string['pluginname'] = 'Charon';
$string['modulename'] = 'Charon';
$string['plugin_settings'] = 'Charon settings';
$string['pluginadministration'] = 'Charon administration';
$string['modulenameplural'] = 'Charons';
$string['capella:addinstance'] = 'Add a new Charon';
$string['plagiarism_no_connection'] = 'Failed to connect to Plagiarism';

/*------------------------*\
   Course settings form
\*------------------------*/
$string['tester_settings'] = 'Tester settings';
$string['presets'] = 'Presets';
$string['course_plagiarism_settings'] = 'Plagiarism settings';
$string['unittests_git'] = 'Unittests Git';
$string['edit_preset'] = 'Edit a preset';
$string['preset_name'] = 'Preset name';
$string['grade_name_prefix'] = 'Grade name prefix';
$string['grade_name_postfix'] = 'Grade name postfix';
$string['id_number_postfix'] = 'ID number postfix';
$string['save_preset'] = 'Save preset';
$string['update_preset'] = 'Update preset';
$string['plagiarism_language_type'] = 'Language type';
$string['plagiarism_gitlab_group'] = 'GitLab group';
$string['plagiarism_gitlab_location'] = 'GitLab project location type';
$string['plagiarism_file_extensions'] = 'File extensions';
$string['plagiarism_moss_passes'] = 'Number of Moss passes';
$string['plagiarism_moss_matches_shown'] = 'Number of Moss matches shown';

$string['unittests_git_helper'] = 'The Git URL in which there are tests for this course. Tests for each assignment '
    . 'should be in the "Project" directory specified in the new Charon form.';
$string['tester_type_cs_helper'] = 'The tester type this course\'s assignments use. Usually the programming language '
    . 'mainly used in the course. Can also be overridden when creating a task.';
$string['preset_name_helper'] = 'Name of the preset. Choose something easy to understand, eg. home tasks.';
$string['tester_extra_cs_helper'] = 'Extra parameters sent to the concrete tester (java, python, etc.). Eg. stylecheck for checking the style. Can also be overridden while creating a task.';
$string['system_extra_cs_helper'] = 'Extra parameters sent to the core system. Eg. send uniid to the tester. Can also be overridden while creating a task.';
$string['max_points_cs_helper'] = 'Maximum points gotten from these types of assignments.';
$string['grading_method_cs_helper'] = 'Grading method used in these assignments.';
$string['grades_helper'] = 'Grades checked in tasks with this preset. Tests_X is for automated tests, Style_X '
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
$string['plagiarism_file_extensions_helper'] = 'Multiple extensions must be separated by comma, for example: .java,.py';
$string['plagiarism_moss_passes_helper'] = 'The maximum amount of source code passages that may appear among the solutions before the lines are ignored and treated as template code. Acceptable range: 1-32767';
$string['plagiarism_moss_matches_shown_helper'] = 'Number of matches Moss will provide. Lowering this will reduce wait times. Acceptable range: 1-50';
$string['plagiarism_create_course'] = 'Course can be created in Plagiarism';
$string['plagiarism_update_course'] = 'Course can be updated in Plagiarism';

/*------------------------*\
   Instance form
\*------------------------*/
$string['naming'] = 'Naming';
$string['task_info'] = 'Task info';
$string['plagiarism_detection'] = 'Plagiarism detection';
$string['task_name'] = 'Task name';
$string['project_folder_name'] = 'Project folder name';
$string['tester_extra'] = 'Extra tester parameters';
$string['system_extra'] = 'Extra system parameters';
$string['tester_type'] = 'Tester type';
$string['grading'] = 'Grading';
$string['grading_method'] = 'Grading method';
$string['grademaps'] = 'Grademaps';
$string['grades'] = 'Grades';
$string['grade_name'] = 'Grade name';
$string['max_points'] = 'Max points';
$string['id_number'] = 'ID number';
$string['grade_persistent'] = 'Persistent';
$string['calculation_formula'] = 'Calculation formula';
$string['preset'] = 'Preset';
$string['unittests_git_charon'] = 'Charon unittests Git';
$string['instance_plagiarism_settings'] = 'Plagiarism settings';

$string['deadline'] = 'Deadline';
$string['percentage'] = 'Percentage';
$string['group'] = 'Group';
$string['duration'] = 'Duration';
$string['labs'] = 'Labs';
$string['teacher'] = 'Teacher';


$string['task_name_helper'] = 'The name for this assignment. A category with this name will be created which will '
    . 'contain this assignment\'s grades.';
$string['project_folder_name_helper'] = 'The folder name for this assignment. Students have to put their code in '
    . 'this folder. This is not shown to students so it should be included in '
    . 'the task description.';
$string['deadlines_helper'] = 'Deadlines for this task. If a submission is submitted after the deadline, the '
    . 'test results will be multiplied by the percentage. Eg. deadline with 0% means '
    . 'that after that deadline the student will receive 0 points for tests.';
$string['deadline_helper'] = 'Deadline date and time.';
$string['percentage_helper'] = 'Max percentage of points after deadline.';
$string['group_helper'] = 'Group for which this deadline applies. WIP';
$string['defense_deadline_helper'] = 'Defense deadline.';
$string['duration_helper'] = 'Defense duration in minutes.';
$string['labs_helper'] = 'Labs where this Charon can be defended.';
$string['teacher_helper'] = 'Student can choose a teacher.';
$string['preset_select_helper'] = 'Any settings can be overridden in the advanced section.';
$string['tester_extra_helper'] = 'Extra parameters sent to the concrete tester (java, python, etc.). Eg. stylecheck for checking the style.';
$string['system_extra_helper'] = 'Extra parameters sent to the core system. Eg. send uniid to the tester.';
$string['tester_type_helper'] = 'Tester type for this task. Usually just the programming language used.';
$string['max_points_helper'] = 'Total points gotten from this assignment.';
$string['calculation_formula_helper'] = 'Formula to use in calculating the total grade for submissions. '
    . 'This uses the same syntax as Moodle formulas. ID numbers from '
    . 'this task\'s grades should be used. Eg. "=sum([[Tests_1]], [[Tests_2]]) * '
    . '[[Style_1]] * [[Custom_1]]"';
$string['grade_name_helper'] = 'Moodle grade name. Eg. EX01 - Tests.';
$string['max_points_grade_helper'] = 'Max points possible to get for this grade.';
$string['id_number_helper'] = 'Unique identifier used in calculating total points using a formula.';
$string['grade_persistent_helper'] = 'Points for this grade carry on to subsequent student Submissions.';
$string['unittests_git_charon_helper'] = 'The Git URL in which there are tests for this Charon';

$string['remove_button_text'] = 'Remove';
$string['add_button_text'] = 'Add';

$string['grouping_selection_helper'] = 'Which grouping is used for student group grading';
$string['grouping'] = "Grouping";

$string['plagiarism_create_charon'] = 'Create Charon in Plagiarism';
$string['plagiarism_update_charon'] = 'Update Charon in Plagiarism';

/*------------------------*\
   Labs form
\*------------------------*/

$string['save'] = "Save";
$string['cancel'] = "Cancel";

$string['lab_info'] = "Lab info";
$string['multiple_labs'] = "Add multiple lab sessions";

$string['start'] = "Start";
$string['end'] = "End";
$string['teachers'] = "Teachers";
$string['weeks'] = "Weeks";

$string['start_helper'] = "Start date and time.";
$string['end_helper'] = "End time.";
$string['teachers_helper'] = "Teachers attending this lab session.";
$string['weeks_helper'] = "Choose weeks when this lab session takes place.";

$string['teachers_placeholder'] = "Select teachers";
$string['weeks_placeholder'] = "Select weeks";

/*------------------------*\
   Assignment view
\*------------------------*/
$string['submission'] = 'Submission';
$string['submissions'] = 'Submissions';
$string['commit_message'] = 'Commit message';
$string['files'] = 'Files';
$string['deadlines'] = 'Deadlines';
$string['defense'] = 'Defense';
$string['after'] = 'After';
$string['percentage'] = 'Percentage';
$string['your_points'] = 'Your points';
$string['total'] = 'Total';
$string['all_groups'] = 'All groups';
$string['tester_feedback'] = 'Tester feedback';
$string['my_registrations'] = 'My registrations';
$string['load_more'] = 'Load more';
$string['edit'] = 'Edit';
$string['cancel'] = 'Cancel';
$string['save'] = 'Save';
$string['charon_popup'] = 'Charon popup';
$string['all_registrations'] = 'All my registrations';
$string['no_registrations'] = 'No Registrations! Press the shield icon to get started.';
$string['table_no_registrations'] = 'Sorry, nothing to display here :(';
$string['registration_deletion_confirmation'] = 'Are you sure you want to delete this item?';
$string['registration_before_error'] = 'You can\'t delete a registration 2 hours before the start!';
$string['charon'] = 'Exercise';
$string['time'] = 'Time';
$string['lab_name'] = 'Lab';
$string['teacher'] = 'Teacher';
$string['location'] = 'Location';
$string['comment'] = 'Comment';
$string['actions'] = 'Actions';
$string['close'] = "Close";
$string['registration_for'] = 'Registration for';
$string['choose_teacher'] = 'Choose a teacher';
$string['my_teacher'] = 'My Teacher';
$string['any_teacher'] = 'Any Teacher';
$string['choose_time'] = 'Choose a time';
$string['select_day'] = 'Select a lab';
$string['select_time'] = 'Select a time';
$string['register'] = 'Register';
$string['language'] = 'Language';
$string['copy'] = 'Copy';
$string['submit'] = 'Submit';
$string['code_editor'] = 'Templates and code submission on page';
$string['code_submission'] = 'Allow code submission on page';
$string['create_source_file'] = '+ Create Source File';
$string['path'] = 'Path';
$string['path_to_file'] = 'Path to file.';
$string['path_warning'] = 'Path should be unique!';
$string['delete'] = 'Delete';
$string['source_file'] = 'Source File:';
$string['insert_file_path'] = 'Insert file path to see it here and edit file content.';
$string['source_files'] = 'Source Files';
$string['reset_to_templates'] = 'Reset templates';
$string['feedback-text-single-submission'] = 'Feedback for this submission';
$string['no-feedback-info'] = 'When a teacher adds feedback for the submission, it will be visible here.';
$string['feedback-text-all-submissions'] = 'Feedback for all submissions for this charon';
$string['showing-mail'] = 'Showing mail';
$string['showing-table'] = 'Showing table';

/*------------------------*\
   Messages
\*------------------------*/

$string['messageprovider:comment'] = 'Notification of a comment for charon submission';
