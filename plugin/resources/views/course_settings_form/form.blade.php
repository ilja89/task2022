@extends('layouts.base')

@section('content')

    <link rel="stylesheet" href="/mod/charon/plugin/public/css/courseSettings.css">

    <script>
        window.settings = {!! isset($settings) ? $settings->toJson() : '{}'!!};
        window.course_id = {!! $course->id !!};
        window.csrf_token = "{!! csrf_token() !!}";
        window.tester_types = {!! $tester_types->toJson() !!};
        window.presets = {!! $presets->toJson() !!};
        window.grading_methods = {!! $grading_methods->toJson() !!};
        window.grade_name_prefixes = {!! $grade_name_prefixes->toJson() !!};
        window.plagiarism_lang_types = {!! $plagiarism_lang_types->toJson() !!};
        window.gitlab_location_types = {!! $gitlab_location_types->toJson() !!};
        window.gitlab_group_types = {!! json_encode($gitlab_group_types) !!};

        window.translations = {
            tester_settings_title: "{{ translate('tester_settings') }}",
            presets_title: "{{ translate('presets') }}",
            plagiarism_title: "{{ translate('course_plagiarism_settings') }}",

            unittests_git_label: "{{ translate('unittests_git') }}",
            tester_type_label: "{{ translate('tester_type') }}",
            edit_preset_label: "{{ translate('edit_preset') }}",
            preset_name_label: "{{ translate('preset_name') }}",
            tester_extra_label: "{{ translate('tester_extra') }}",
            system_extra_label: "{{ translate('system_extra') }}",
            max_points_label: "{{ translate('max_points') }}",
            grading_method_label: "{{ translate('grading_method') }}",
            grades_label: "{{ translate('grades') }}",
            grade_name_label: "{{ translate('grade_name') }}",
            grade_name_prefix_label: "{{ translate('grade_name_prefix') }}",
            grade_name_postfix_label: "{{ translate('grade_name_postfix') }}",
            id_number_postfix_label: "{{ translate('id_number_postfix') }}",
            calculation_formula_label: "{{ translate('calculation_formula') }}",
            plagiarism_lang_label: "{{ translate('plagiarism_language_type') }}",
            plagiarism_gitlab_group_label: "{{ translate('plagiarism_gitlab_group') }}",
            plagiarism_gitlab_location_label: "{{ translate('plagiarism_gitlab_location') }}",
            plagiarism_file_extensions_label: "{{ translate('plagiarism_file_extensions') }}",
            plagiarism_moss_passes_label: "{{ translate('plagiarism_moss_passes') }}",
            plagiarism_moss_matches_shown_label: "{{ translate('plagiarism_moss_matches_shown') }}",

            save_preset: "{{ translate('save_preset') }}",
            update_preset: "{{ translate('update_preset') }}",

            unittests_git_helper: "{{ translate('unittests_git_helper') }}",
            tester_type_helper: "{{ translate('tester_type_cs_helper') }}",
            preset_name_helper: "{{ translate('preset_name_helper') }}",
            tester_extra_cs_helper: "{{ translate('tester_extra_cs_helper') }}",
            system_extra_cs_helper: "{{ translate('system_extra_cs_helper') }}",
            max_points_cs_helper: "{{ translate('max_points_cs_helper') }}",
            grading_method_cs_helper: "{{ translate('grading_method_cs_helper') }}",
            grades_cs_helper: "{{ translate('grades_helper') }}",
            grade_name_cs_helper: "{{ translate('grade_name_cs_helper') }}",
            max_points_grade_cs_helper: "{{ translate('max_points_grade_cs_helper') }}",
            id_number_postfix_helper: "{{ translate('id_number_postfix_helper') }}",
            calculation_formula_cs_helper: "{{ translate('calculation_formula_cs_helper') }}",
            plagiarism_file_extensions_helper: "{{ translate('plagiarism_file_extensions_helper') }}",
            plagiarism_moss_passes_helper: "{{ translate('plagiarism_moss_passes_helper') }}",
            plagiarism_moss_matches_shown_helper: "{{ translate('plagiarism_moss_matches_shown_helper') }}"

        };
    </script>

    <div id="app">
        <h1>Charon settings for {{ $course->shortname }}</h1>

        <course-settings-form
                :form="form"
                :csrf_token="getCsrfToken()">
        </course-settings-form>
    </div>

    <script src="/mod/charon/plugin/public/js/courseSettings.js"></script>

@endsection
