@extends('layouts.base')

@section('content')

    <div id="app">
        <course-settings-form
                tester_settings_title="{{ translate('tester_settings') }}"
                presets_title="{{ translate('presets_title') }}"
        >
        </course-settings-form>
    </div>

    <script src="/mod/charon/plugin/public/js/app.js"></script>

@endsection
