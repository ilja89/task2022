<?php

/*------------------------*\
   General
\*------------------------*/
$string['pluginname'] = 'Charon';
$string['modulename'] = 'Charon';
$string['plugin_settings'] = 'Charoni seaded';
$string['pluginadministration'] = 'Charoni administreerimine';
$string['modulenameplural'] = 'Charonid';
$string['capella:addinstance'] = 'Lisa uus charon';

/*------------------------*\
   Course settings form
\*------------------------*/
$string['tester_settings'] = 'Testeri seaded';
$string['presets'] = 'Presetid';
$string['unittests_git'] = 'Testide Git';
$string['edit_preset'] = 'Muuda preseti';
$string['preset_name'] = 'Preseti nimi';
$string['grade_name_prefix'] = 'Hinde nime eesliide';
$string['grade_name_postfix'] = 'Hinde nime lõpp';
$string['id_number_postfix'] = 'ID numbri lõpp';
$string['save_preset'] = 'Salvesta preset';
$string['update_preset'] = 'Uuenda preseti';

$string['unittests_git_helper'] = 'Giti URL kus asuvad selle kursuse testid. Testid peavad olema sama nimega kaustas '
    . 'kuhu tudengid koodi panevad.';
$string['tester_type_helper'] = 'Selle kursuse ülesannete testeri tüüp, programmeerimise keel. Ülesannet luues saab '
    . 'seda muuta.';
$string['preset_name_helper'] = 'Preseti nimi. Nt. kodutööd.';
$string['tester_extra_cs_helper'] = 'Konkreetsele testerile (java, python, jne.) saadetavad ekstra parameetrid. Ülesannet luues saab seda muuta.';
$string['system_extra_cs_helper'] = 'Testeri süsteemile saadetavad parameetrid. Ülesannet luues saab seda muuta.';
$string['max_points_cs_helper'] = 'Selle presetiga loodud ülesannete maksimum punktid.';
$string['grading_method_cs_helper'] = 'Selle presetiga loodud ülesannete hindamise meetod. Prefer last: hinne '
    . 'muudetakse alati, kui pole kinnitatud esitust. Prefer best: hinne muudetakse '
    . 'siis, kui hinne on parem eelnevast ning pole kinnitatud esitust.';
$string['grades_cs_helper'] = 'Selle presetiga loodud ülesannetes kontrollitavad hinded. Tests_X on automaattestide '
    . 'kontroll, Style_X on stiili kontroll, Custom_X hindeid peab õpetaja manuaalselt hindama.';
$string['grade_name_cs_helper'] = 'Hinde nimi, näidatakse Moodlis õpilastele. Projekti kaust või ülesande nimi on prefix. '
    . 'Nt "EX01 - Testid" kus "EX01" on kausta nimi ja " - Testid" on postfix.';
$string['max_points_grade_cs_helper'] = 'Selle hinde maksimum punktid. Nt iga Tests_1 hinde maksimum punktid on 10.';
$string['id_number_postfix_helper'] = 'ID number on unikaalne identifikaator mida kasutatakse ülesande punktide arvutamiseks.'
    . 'Projekti folder on prefix. Nt "EX01_testid" kus "_testid" on siia kirjutatud väärtus.';
$string['calculation_formula_cs_helper'] = 'Valem mille järgi ülesande kokkuvõttev hinne arvutatakse. Kasutab sama süntaksit '
    . 'mis Moodle oma valemites kuid grade itemite id-d tuleb asendada hinde tüübiga. '
    . 'Nt "=sum([[Tests_1]], [[Tests_2]]) * [[Style_1]] * [[Custom_1]]".';

/*------------------------*\
   Instance form
\*------------------------*/
$string['naming'] = 'Nimetamine';
$string['task_info'] = 'Ülesande andmed';
$string['plagiarism_detection'] = 'Plagiarismikontroll';
$string['task_name'] = 'Ülesande nimi';
$string['project_folder_name'] = 'Projekti kausta nimi';
$string['tester_extra'] = 'Ekstra parameetrid testerile';
$string['system_extra'] = 'Ekstra parameetrid süsteemile';
$string['tester_type'] = 'Testeri tüüp';
$string['grading'] = 'Hindamine';
$string['grading_method'] = 'Hindamise meetod';
$string['grademaps'] = 'Hinded';
$string['grades'] = 'Hinded';
$string['grade_name'] = 'Hinde nimi';
$string['max_points'] = 'Maksimum punktid';
$string['id_number'] = 'ID number';
$string['grade_persistent'] = 'Püsiv';
$string['calculation_formula'] = 'Arvutuse valem';
$string['preset'] = 'Preset';
$string['plagiarism_service'] = 'Plagiarismi teenus';
$string['plagiarism_enabled'] = 'Plagiarismikontroll aktiivne';
$string['plagiarism_resource_provider_repository'] = 'Plagiarismi Giti repositoorium';
$string['plagiarism_resource_provider_private_key'] = 'Plagiarismi repositooriumi privaatvõti';
$string['plagiarism_includes'] = 'Plagiarismikontrolli kontrollitavad failid';

$string['deadline'] = 'Tähtaeg';
$string['percentage'] = 'Protsent';
$string['group'] = 'Grupp';
$string['duration'] = 'Kestus';
$string['labs'] = 'Praktikumid';
$string['teacher'] = 'Õppejõud';

$string['task_name_helper'] = 'Ülesande pealkiri. Selle nimega luuakse kategooria kuhu tekivad ülesande hinded, '
    . 'grade itemid.';
$string['project_folder_name_helper'] = 'Kausta nimi, kuhu tudengid peavad oma lahenduse panema. Seda ei näidata tudengile, '
    . 'niiet selle peaks kirjeldusse lisama.';
$string['deadlines_helper'] = 'Tähtajad, kui esitus on hiljem kui tähtaeg, siis korrutatakse testide tulemus '
    . 'protsendiga. Nt 0%-ga deadline tähendab, et  pärast seda esitades saab hindeks 0.';
$string['deadline_helper'] = 'Tähtaja kuupäev ja aeg.';
$string['percentage_helper'] = 'Protsent, mis võetakse hindeks kui pärast tähtaega töö esitatakse.';
$string['defense_deadline_helper'] = 'Kaitsmise tähtaeg.';
$string['duration_helper'] = 'Kaitsmise kestus minutites.';
$string['labs_helper'] = 'Praktikumid, kus seda Charonit kaitsta saab.';
$string['teacher_helper'] = 'Tudeng saab kaitsmiseks õppejõu valida.';
$string['group_helper'] = 'Grupp kellele see hinne kehtib. Praegu ei tööta.';
$string['preset_select_helper'] = 'Kõiki seadeid saab "advanced" sektsioonis muuta.';
$string['tester_extra_helper'] = 'Konkreetsele testerile (java, python, jne.) saadetavad ekstra parameetrid.';
$string['system_extra_helper'] = 'Testeri süsteemile saadetavad parameetrid.';
$string['tester_type_helper'] = 'Testeri tüüp, tavaliselt programmeerimise keel.';
$string['calculation_formula_helper'] = 'Valem mille järgi ülesande kokkuvõttev hinne arvutatakse. Kasutab sama süntaksit '
    . 'mis Moodle oma valemites kuid grade itemite id-d tuleb asendada hinde tüübiga. '
    . 'Nt "=sum([[Tests_1]], [[Tests_2]]) * [[Style_1]] * [[Custom_1]]".';
$string['grade_name_helper'] = 'Moodli hinde nimetus. Nt EX01 - Testid.';
$string['max_points_grade_helper'] = 'Selle hinde matsimum punktid.';
$string['id_number_helper'] = 'Unikaalne identifikaator mida kasutatakse ülesande punktide arvutamiseks.';
$string['grade_persistent_helper'] = 'Punktid kanduvad edasi järgmistele tudengi esitustele.';
$string['plagiarism_service_helper'] = 'Plagiarismikontrolli teenus, mida kasutatakse plagiarismi teenuse poolt.';
$string['plagiarism_resource_provider_repository_helper'] = 'Giti repositoorium, milles olevaid faile kontrollitakse plagiarismi teenuse poolt.';
$string['plagiarism_resource_provider_private_key_helper'] = 'Privaatvõti, mida kasutatakse repositooriumi failidele '
    . 'ligipääsuks.';
$string['plagiarism_includes_helper'] = 'Regex, millega saab filtreerida faile, mida plagiaadikontroll kontrollib. '
    . 'Näiteks, regex ".*/EX13.*", kontrollib kõiki faile EX13 kaustades.';

$string['remove_button_text'] = 'Eemalda';
$string['add_button_text'] = 'Lisa';

$string['grouping'] = "Grupeering";
$string['grouping_selection_helper'] = 'Millist grupeeringut kasutada tudengigrupi automaathindamiseks';

/*------------------------*\
        Labs form
\*------------------------*/

$string['save'] = "Salvesta";
$string['cancel'] = "Tühista";

$string['lab_info'] = "Praktikumi info";
$string['multiple_labs'] = "Lisa mitu praktikumi sessiooni";

$string['start'] = "Algus";
$string['end'] = "Lõpp";
$string['teachers'] = "Õppejõud";
$string['weeks'] = "Nädalad";

$string['start_helper'] = "Alguse kuupäev ja aeg.";
$string['end_helper'] = "Lõpu aeg.";
$string['teachers_helper'] = "Õppejõud, kes selles praktikumis kohal on.";
$string['weeks_helper'] = "Vali nädalad, millal see praktikum aset leiab.";

$string['teachers_placeholder'] = "Vali õppejõud";
$string['weeks_placeholder'] = "Vali nädalad";

/*------------------------*\
   Assignment view
\*------------------------*/
$string['submission'] = 'Esitus';
$string['submissions'] = 'Esitused';
$string['commit_message'] = 'Commiti kommentaar';
$string['files'] = 'Failid';
$string['deadlines'] = 'Tähtajad';
$string['defense'] = 'Kaitsmine';
$string['after'] = 'Pärast';
$string['percentage'] = 'Protsent';
$string['your_points'] = 'Sinu punktid';
$string['total'] = 'Kokku';
$string['all_groups'] = 'Kõik grupid';
$string['tester_feedback'] = 'Testeri tagasiside';
$string['my_registrations'] = 'Minu registreerimised';
$string['load_more'] = 'Lae rohkem';
$string['edit'] = 'Redigeeri';
$string['cancel'] = 'Tühista';
$string['save'] = 'Salvesta';
$string['charon_popup'] = 'Charoni hüpikaken';
$string['all_registrations'] = 'Minu kõik registreerimised';
$string['no_registrations'] = 'Registreerimisi pole. Vajutage kilbi ikooni, et registreerida koodi ette näitamisele.';
$string['table_no_registrations'] = 'Tühjus...';
$string['registration_deletion_confirmation'] = 'Oled sa kindel, et tahad registreerimist tühistada?';
$string['charon'] = 'Ülesanne';
$string['time'] = 'Aeg';
$string['lab_name'] = 'Praktikum';
$string['teacher'] = 'Õpetaja';
$string['location'] = 'Asukoht';
$string['comment'] = 'Kommentaar';
$string['actions'] = 'Tegevused';
$string['close'] = "Sulge";
$string['registration_for'] = 'Registreerimised ülesandele';
$string['choose_teacher'] = 'Õpetaja valik';
$string['my_teacher'] = 'Minu õpetaja';
$string['any_teacher'] = 'Suvaline õpetaja';
$string['choose_time'] = 'Vali koodi ettenäitamise aeg';
$string['select_day'] = 'Vali praktikum';
$string['select_time'] = 'Vali kellaaeg';
$string['register'] = 'Registreeri';
$string['progress'] = 'Progress';
$string['language'] = 'Keel';
$string['copy'] = 'Kopeeri';
$string['submit'] = 'Esita';
$string['code_editor'] = 'Mallid ja koodi esitamine veebilehel';
$string['code_submission'] = 'Luba koodi esitamine veebilehel';
$string['create_source_file'] = '+ Loo lähtefail';
$string['path'] = 'Tee';
$string['path_to_file'] = 'Tee failini.';
$string['path_warning'] = 'Tee failini peab olla ainulaadne!';
$string['delete'] = 'Kustuta';
$string['source_file'] = 'Lähtefail:';
$string['insert_file_path'] = 'Sisestage faili tee, et seda siin näha ja faili sisu muuta.';
$string['source_files'] = 'Lähtefailid';
$string['reset_to_templates'] = 'Taasta mallid';
$string['feedback-text'] = 'Tagasiside';
$string['no-feedback-info'] = 'Kui õpetaja/juhendaja lisab koodi kohta tagasisidet, on see nähtav siin.';
