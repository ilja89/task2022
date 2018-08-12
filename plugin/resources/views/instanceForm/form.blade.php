<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">

<script>
    window.gradingMethods = {!! $gradingMethods->toJson() !!};
    window.testerTypes = {!! $testerTypes->toJson() !!};
    window.instance = {!! isset($charon) ? $charon->toJson() : '{}'!!};
    window.update = {{ isset($update) ? 'true' : 'false' }};
    window.courseSettings = {!! $courseSettings !== null ? $courseSettings->toJson() : '{}' !!};
    window.presets = {!! $presets !== null ? $presets->toJson() : '[]' !!};
    window.isEditing = {!! isset($charon) ? 'true' : 'false' !!};
    window.groups = {!! $groups->toJson() !!};

    window.translations = {
        naming_title: "{{ translate('naming') }}",
        task_info_title: "{{ translate('task_info') }}",
        grading_title: "{{ translate('grading') }}",

        task_name_label: "{{ translate('task_name') }}",
        project_folder_name_label: "{{ translate('project_folder_name') }}",
        tester_extra_label: "{{ translate('tester_extra') }}",
        system_extra_label: "{{ translate('system_extra') }}",
        tester_type_label: "{{ translate('tester_type') }}",
        grading_method_label: "{{ translate('grading_method') }}",
        grades_label: "{{ translate('grades') }}",
        grade_name_label: "{{ translate('grade_name') }}",
        max_points_label: "{{ translate('max_points') }}",
        id_number_label: "{{ translate('id_number') }}",
        calculation_formula_label: "{{ translate('calculation_formula') }}",
        preset_label: "{{ translate('preset') }}",
        deadlines: "{{ translate('deadlines') }}",
        deadline_label: "{{ translate('deadline') }}",
        percentage_label: "{{ translate('percentage') }}",
        group_label: "{{ translate('group') }}",

        task_name_helper: "{{ translate('task_name_helper') }}",
        project_folder_name_helper: "{{ translate('project_folder_name_helper') }}",
        deadlines_helper: "{{ translate('deadlines_helper') }}",
        deadline_helper: "{{ translate('deadline_helper') }}",
        percentage_helper: "{{ translate('percentage_helper') }}",
        group_helper: "{{ translate('group_helper') }}",
        preset_select_helper: "{{ translate('preset_select_helper') }}",
        tester_extra_helper: "{{ translate('tester_extra_helper') }}",
        system_extra_helper: "{{ translate('system_extra_helper') }}",
        tester_type_helper: "{{ translate('tester_type_helper') }}",
        grading_method_helper: "{{ translate('grading_method_cs_helper') }}",
        grades_helper: "{{ translate('grades_helper') }}",
        max_points_helper: "{{ translate('max_points_helper') }}",
        calculation_formula_helper: "{{ translate('calculation_formula_helper') }}",
        grade_name_helper: "{{ translate('grade_name_helper') }}",
        max_points_grade_helper: "{{ translate('max_points_grade_helper') }}",
        id_number_helper: "{{ translate('id_number_helper') }}",

    };

    window.courseSettingsUrl = "{{ $courseSettingsUrl }}";
    window.moduleSettingsUrl = "{{ $moduleSettingsUrl }}";
</script>

<div id="app">

    {{ csrf_field() }}

    <instance-form
            :form="form">
    </instance-form>

</div>

<script src="/mod/charon/plugin/public/js/instanceForm.js"></script>

<script>
    if (window.moduleSettingsUrl.length) {
        window.VueEvent.$emit(
            'show-notification',
            'Tester URL not set in module settings!<br>Ask an administrator to set it <a href="' + window.moduleSettingsUrl + '">here</a>.',
            'danger',
            null
        )
    } else if (window.courseSettingsUrl.length) {
        window.VueEvent.$emit(
            'show-notification',
            'Unittests Git URL not set in course settings! Set it <a href="' + window.courseSettingsUrl + '">here</a>.',
            'danger',
            null
        )
    }
</script>
