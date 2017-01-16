<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">

<script>
    window.gradeTypes = {!! $gradeTypes->toJson() !!};
    window.gradingMethods = {!! $gradingMethods->toJson() !!};
    window.testerTypes = {!! $testerTypes->toJson() !!};
    window.instance = {!! isset($charon) ? $charon->toJson() : '{}'!!};

    window.translations = {
        naming_title: "{{ translate('naming') }}",
        task_info_title: "{{ translate('task_info') }}",
        grading_title: "{{ translate('grading') }}",

        task_name_label: "{{ translate('task_name') }}",
        project_folder_name_label: "{{ translate('project_folder_name') }}",
        tester_type_label: "{{ translate('tester_type') }}",
        grading_method_label: "{{ translate('grading_method') }}",

    };
</script>

<div id="app">

    <instance-form
            :form="form"
    >
    </instance-form>

</div>

<script src="/mod/charon/plugin/public/js/instanceForm.js"></script>
