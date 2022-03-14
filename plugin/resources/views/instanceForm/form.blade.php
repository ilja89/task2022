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
    window.groupings = {!! $groupings->toJson() !!};
    window.plagiarismServices = {!! $plagiarismServices->toJson() !!};
    // ...

    window.translations = {
        naming_title: "{{ translate('naming') }}",
        plagiarism_detection: "{{ translate('plagiarism_detection') }}",
        task_info_title: "{{ translate('task_info') }}",
        grading_title: "{{ translate('grading') }}",

        existing_tasks_label: "{{ translate('existing_tasks') }}",
        task_name_label: "{{ translate('task_name') }}",
        project_folder_name_label: "{{ translate('project_folder_name') }}",
        tester_extra_label: "{{ translate('tester_extra') }}",
        system_extra_label: "{{ translate('system_extra') }}",
        unittests_git_charon_label: "{{ translate('unittests_git_charon') }}",
        tester_type_label: "{{ translate('tester_type') }}",
        grading_method_label: "{{ translate('grading_method') }}",
        grades_label: "{{ translate('grades') }}",
        grade_name_label: "{{ translate('grade_name') }}",
        max_points_label: "{{ translate('max_points') }}",
        id_number_label: "{{ translate('id_number') }}",
        grade_persistent_label: "{{ translate('grade_persistent') }}",
        calculation_formula_label: "{{ translate('calculation_formula') }}",
        preset_label: "{{ translate('preset') }}",
        deadlines: "{{ translate('deadlines') }}",
        defense: "{{ translate('defense') }}",
        deadline_label: "{{ translate('deadline') }}",
        percentage_label: "{{ translate('percentage') }}",
        duration_label: "{{ translate('duration') }}",
        labs_label: "{{ translate('labs') }}",
        teacher_label: "{{ translate('teacher') }}",
        group_label: "{{ translate('group') }}",
        plagiarism_service_label: "{{ translate('plagiarism_service') }}",
        plagiarism_enabled: "{{ translate('plagiarism_enabled') }}",
        plagiarism_resource_provider_repository: "{{ translate('plagiarism_resource_provider_repository') }}",
        plagiarism_resource_provider_private_key: "{{ translate('plagiarism_resource_provider_private_key') }}",
        plagiarism_includes: "{{ translate('plagiarism_includes') }}",
        grouping: "{{ translate('grouping') }}",
        grouping_selection_helper: "{{ translate('grouping_selection_helper') }}",

        task_name_helper: "{{ translate('task_name_helper') }}",
        project_folder_name_helper: "{{ translate('project_folder_name_helper') }}",
        deadlines_helper: "{{ translate('deadlines_helper') }}",
        deadline_helper: "{{ translate('deadline_helper') }}",
        percentage_helper: "{{ translate('percentage_helper') }}",
        defense_deadline_helper: "{{ translate('defense_deadline_helper') }}",
        duration_helper: "{{ translate('duration_helper') }}",
        labs_helper: "{{ translate('labs_helper') }}",
        teacher_helper: "{{ translate('teacher_helper') }}",
        group_helper: "{{ translate('group_helper') }}",
        preset_select_helper: "{{ translate('preset_select_helper') }}",
        tester_extra_helper: "{{ translate('tester_extra_helper') }}",
        system_extra_helper: "{{ translate('system_extra_helper') }}",
        unittests_git_charon_helper: "{{ translate('unittests_git_charon_helper') }}",
        tester_type_helper: "{{ translate('tester_type_helper') }}",
        grading_method_helper: "{{ translate('grading_method_cs_helper') }}",
        grades_helper: "{{ translate('grades_helper') }}",
        max_points_helper: "{{ translate('max_points_helper') }}",
        calculation_formula_helper: "{{ translate('calculation_formula_helper') }}",
        grade_name_helper: "{{ translate('grade_name_helper') }}",
        max_points_grade_helper: "{{ translate('max_points_grade_helper') }}",
        id_number_helper: "{{ translate('id_number_helper') }}",
        grade_persistent_helper: "{{ translate('grade_persistent_helper') }}",
        plagiarism_service_helper: "{{ translate('plagiarism_service_helper') }}",
        plagiarism_resource_provider_repository_helper: "{{ translate('plagiarism_resource_provider_repository_helper') }}",
        plagiarism_resource_provider_private_key_helper: "{{ translate('plagiarism_resource_provider_private_key_helper') }}",
        plagiarism_includes_helper: "{{ translate('plagiarism_includes_helper') }}",

        remove: "{{ translate('remove_button_text') }}",
        add: "{{ translate('add_button_text') }}",

        codeEditorSection: "{{ translate('code_editor') }}",
        allowCodeSubmission: "{{ translate('code_submission') }}",
        createSourceFileButton: "{{ translate('create_source_file') }}",
        path: "{{ translate('path') }}",
        pathToFileHelper: "{{ translate('path_to_file') }}",
        pathWarning: "{{ translate('path_warning') }}",
        deleteButton: "{{ translate('delete') }}",
        sourceFile: "{{ translate('source_file') }}",
        insertFilePath: "{{ translate('insert_file_path') }}",
        programmingLanguage: "{{ translate('language') }}",
        sourceFiles: "{{ translate('source_files') }}"
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
            'Tester URL not set in module settings! Redirecting.',
            'danger',
            null
        )
        setTimeout(function () {
            window.location.replace(window.moduleSettingsUrl);
        }, 2000);
    } else if (window.courseSettingsUrl.length) {
        window.VueEvent.$emit(
            'show-notification',
            'Unittests Git URL not set in course settings! Redirecting.',
            'danger',
            null
        )
        setTimeout(function () {
            window.location.replace(window.courseSettingsUrl);
        }, 2000);
    }
</script>
