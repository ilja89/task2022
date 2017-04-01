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
$string['tester_settings']    = 'Testeri seaded';
$string['presets']            = 'Presetid';
$string['unittests_git']      = 'Testide Git';
$string['edit_preset']        = 'Muuda preseti';
$string['preset_name']        = 'Preseti nimi';
$string['grade_name_prefix']  = 'Hinde nime eesliide';
$string['grade_name_postfix'] = 'Hinde nime lõpp';
$string['id_number_postfix']  = 'ID numbri lõpp';
$string['save_preset']        = 'Salvesta preset';
$string['update_preset']      = 'Uuenda preseti';

$string['unittests_git_helper']          = 'Giti URL kus asuvad selle kursuse testid. Testid peavad olema sama nimega kaustas '
                                           . 'kuhu tudengid koodi panevad.';
$string['tester_type_helper']            = 'Selle kursuse ülesannete testeri tüüp, programmeerimise keel. Ülesannet luues saab '
                                           . 'seda muuta.';
$string['preset_name_helper']            = 'Preseti nimi. Nt. kodutööd.';
$string['extra_cs_helper']               = 'Testerile saadetavad extra parameetrid. Ülesannet luues saab seda muuta.';
$string['max_points_cs_helper']          = 'Selle presetiga loodud ülesannete maksimum punktid.';
$string['grading_method_cs_helper']      = 'Selle presetiga loodud ülesannete hindamise meetod. Prefer last: hinne '
                                           . 'muudetakse alati, kui pole kinnitatud esitust. Prefer best: hinne muudetakse '
                                           . 'siis, kui hinne on parem eelnevast ning pole kinnitatud esitust.';
$string['grades_cs_helper']              = 'Selle presetiga loodud ülesannetes kontrollitavad hinded. Tests_X on automaattestide '
                                           . 'kontroll, Style_X on stiili kontroll, Custom_X hindeid peab õpetaja manuaalselt hindama.';
$string['grade_name_cs_helper']          = 'Hinde nimi, näidatakse Moodlis õpilastele. Projekti kaust või ülesande nimi on prefix. '
                                           . 'Nt "EX01 - Testid" kus "EX01" on kausta nimi ja " - Testid" on postfix.';
$string['max_points_grade_cs_helper']    = 'Selle hinde maksimum punktid. Nt iga Tests_1 hinde maksimum punktid on 10.';
$string['id_number_postfix_helper']      = 'ID number on unikaalne identifikaator mida kasutatakse ülesande punktide arvutamiseks.'
                                           . 'Projekti folder on prefix. Nt "EX01_testid" kus "_testid" on siia kirjutatud väärtus.';
$string['calculation_formula_cs_helper'] = 'Valem mille järgi ülesande kokkuvõttev hinne arvutatakse. Kasutab sama süntaksit '
                                           . 'mis Moodle oma valemites kuid grade itemite id-d tuleb asendada hinde tüübiga. '
                                           . 'Nt "=sum([[Tests_1]], [[Tests_2]]) * [[Style_1]] * [[Custom_1]]".';

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

$string['deadline']   = 'Tähtaeg';
$string['percentage'] = 'Protsent';
$string['group']      = 'Grupp';

$string['task_name_helper']           = 'The name for this assignment. A category with this name will be created which will '
                                        . 'contain this assignment\'s grades.';
$string['project_folder_name_helper'] = 'The folder name for this assignment. Students have to put their code in '
                                        . 'this folder. This is not shown to students so it should be included in '
                                        . 'the task description.';
$string['deadlines_helper']           = 'Deadlines for this task. If a submission is submitted after the deadline, the '
                                        . 'test results will be multiplied by the percentage. Eg. deadline with 0% means '
                                        . 'that after that deadline the student will receive 0 points for tests.';
$string['deadline_helper']            = 'Deadline date and time.';
$string['percentage_helper']          = 'Percentage of points multiplied if submission is after deadline.';
$string['group_helper']               = 'Group for which this deadline applies. Currently does not work.';
$string['preset_select_helper']       = 'Any settings can be overridden in the advanced section.';
$string['extra_helper']               = 'Extra parameters sent to the tester. Eg. stylecheck for checking the style.';
$string['tester_type_helper']         = 'Tester type for this task. Usually just the programming language used.';
$string['calculation_formula_helper'] = 'Formula to use in calculating the total grade for submissions. '
                                        . 'This uses the same syntax as Moodle formulas. ID numbers from '
                                        . 'this task\'s grades should be used. Eg. "=sum([[Tests_1]], [[Tests_2]]) * '
                                        . '[[Style_1]] * [[Custom_1]]"';
$string['grade_name_helper']          = 'Moodle grade name. Eg. EX01 - Tests.';
$string['max_points_grade_helper']    = 'Max points possible to get for this grade.';
$string['id_number_helper']           = 'Unique identifier used in calculating total points using a formula.';


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
