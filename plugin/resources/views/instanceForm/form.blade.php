<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">

<div id="app">

    <instance-form
            naming_title="{{ translate('naming') }}"
            task_name_label="{{ translate('task_name') }}"
            task_name_value="{{ isset($charon) ? $charon->name : '' }}"
    >
    </instance-form>

</div>

<script src="/mod/charon/plugin/public/js/app.js"></script>
