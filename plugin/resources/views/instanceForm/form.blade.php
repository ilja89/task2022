<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">

<script>
    window.gradeTypes = {!! $gradeTypes->toJson() !!};
    window.gradingMethods = {!! $gradingMethods->toJson() !!};
    window.testerTypes = {!! $testerTypes->toJson() !!};
    window.instance = {!! isset($charon) ? $charon->toJson() : '{}'!!};

    window.translations = {
        naming_title: "{{ translate('naming') }}",
        task_info_title: "{{ translate('task_info') }}",

        task_name_label: "{{ translate('task_name') }}",
        project_folder_name_label: "{{ translate('project_folder_name') }}",
        tester_type_label: "{{ translate('tester_type') }}",
    };
</script>

<div id="app">

    <instance-form
            :grade_types="grade_types"
            :grading_methods="grading_methods"
            :tester_types="tester_types"
            :form="form"
    >
    </instance-form>

</div>

<script src="/mod/charon/plugin/public/js/instanceForm.js"></script>
