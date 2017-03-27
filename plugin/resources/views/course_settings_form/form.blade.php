@extends('layouts.base')

@section('content')

    <link rel="stylesheet" href="/mod/charon/plugin/public/css/courseSettings.css">

    <script>
        window.settings = {!! isset($settings) ? $settings->toJson() : '{}'!!};
        window.course_id = {!! $course_id !!};
        window.csrf_token = "{!! csrf_token() !!}";
        window.tester_types = {!! $tester_types->toJson() !!};
        window.presets = {!! $presets->toJson() !!};
        window.grading_methods = {!! $grading_methods->toJson() !!};
        window.grade_name_prefixes = {!! $grade_name_prefixes->toJson() !!};

        window.translations = {
            tester_settings_title: "{{ translate('tester_settings') }}",
            presets_title: "{{ translate('presets') }}",

            unittests_git_label: "{{ translate('unittests_git') }}",
            tester_type_label: "{{ translate('tester_type') }}",
            edit_preset_label: "{{ translate('edit_preset') }}",
            preset_name_label: "{{ translate('preset_name') }}",
            extra_label: "{{ translate('extra') }}",
            max_points_label: "{{ translate('max_points') }}",
            grading_method_label: "{{ translate('grading_method') }}",
            grades_label: "{{ translate('grades') }}",
            grade_name_label: "{{ translate('grade_name') }}",
            grade_name_prefix_label: "{{ translate('grade_name_prefix') }}",
            grade_name_postfix_label: "{{ translate('grade_name_postfix') }}",
            id_number_postfix_label: "{{ translate('id_number_postfix') }}",
            calculation_formula_label: "{{ translate('calculation_formula') }}",

            save_preset: "{{ translate('save_preset') }}",
            update_preset: "{{ translate('update_preset') }}",
        };
    </script>

    <div id="app">
        <course-settings-form
                :form="form"
                :csrf_token="getCsrfToken()">
        </course-settings-form>
    </div>

    <script src="/mod/charon/plugin/public/js/courseSettings.js"></script>

@endsection
