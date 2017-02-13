@extends('layouts.base')

@section('content')

    <script>
        window.settings = {!! isset($settings) ? $settings->toJson() : '{}'!!};
        window.course_id = {!! $course_id !!};
        window.csrf_token = "{!! csrf_token() !!}";
        window.tester_types = {!! $tester_types->toJson() !!};

        window.translations = {
            tester_settings_title: "{{ translate('tester_settings') }}",
            presets_title: "{{ translate('presets') }}",

            unittests_git_label: "{{ translate('unittests_git') }}",
            tester_type_label: "{{ translate('tester_type') }}",
        }
    </script>

    <div id="app">
        <course-settings-form
                :form="form"
                :csrf_token="getCsrfToken()">
        </course-settings-form>
    </div>

    <script src="/mod/charon/plugin/public/js/courseSettings.js"></script>

@endsection
