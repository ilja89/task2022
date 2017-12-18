<?php

/*------------------------*\
   General
\*------------------------*/
$string['pluginname']           = 'Submission';
$string['modulename']           = 'Submission';
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

$string['task_name_helper']           = 'Ülesande pealkiri. Selle nimega luuakse kategooria kuhu tekivad ülesande hinded, '
                                        . 'grade itemid.';
$string['project_folder_name_helper'] = 'Kausta nimi, kuhu tudengid peavad oma lahenduse panema. Seda ei näidata tudengile, '
                                        . 'niiet selle peaks kirjeldusse lisama.';
$string['deadlines_helper']           = 'Tähtajad, kui esitus on hiljem kui tähtaeg, siis korrutatakse testide tulemus '
                                        . 'protsendiga. Nt 0%-ga deadline tähendab, et  pärast seda esitades saab hindeks 0.';
$string['deadline_helper']            = 'Tähtaja kuupäev ja aeg.';
$string['percentage_helper']          = 'Protsent, mis võetakse hindeks kui pärast tähtaega töö esitatakse.';
$string['group_helper']               = 'Grupp kellele see hinne kehtib. Praegu ei tööta.';
$string['preset_select_helper']       = 'Kõiki seadeid saab "advanced" sektsioonis muuta.';
$string['extra_helper']               = 'Testerile saadetavad extra parameetrid. Nt "stylecheck" käivitab stiili kontrolli.';
$string['tester_type_helper']         = 'Testeri tüüp, tavaliselt programmeerimise keel.';
$string['calculation_formula_helper'] = 'Valem mille järgi ülesande kokkuvõttev hinne arvutatakse. Kasutab sama süntaksit '
                                        . 'mis Moodle oma valemites kuid grade itemite id-d tuleb asendada hinde tüübiga. '
                                        . 'Nt "=sum([[Tests_1]], [[Tests_2]]) * [[Style_1]] * [[Custom_1]]".';
$string['grade_name_helper']          = 'Moodli hinde nimetus. Nt EX01 - Testid.';
$string['max_points_grade_helper']    = 'Selle hinde matsimum punktid.';
$string['id_number_helper']           = 'Unikaalne identifikaator mida kasutatakse ülesande punktide arvutamiseks.';


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
$string['your_points']    = 'Sinu punktid';
$string['total']          = 'Kokku';
$string['all_groups']     = 'Kõik grupid';
