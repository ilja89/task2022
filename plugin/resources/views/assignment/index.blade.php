{!! $header !!}

<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1 class="title">{{ $charon->name }}</h1>

<div class="columns assignment-columns" id="app">

    <div class="column is-two-thirds assignment-content content" v-highlightjs>

        {!! rewritePluginTextUrls($charon->description, 'description', $course_module_id) !!}

        @if ($can_edit)
            <div class="edit-container">
                <a class="button is-link" href="/course/modedit.php?update={{ $charon->courseModule()->id }}&return=1&sr=0">
                    Edit
                </a>
                <a class="button is-link" href="/mod/charon/courses/{{$charon->course}}/popup">
                    Charon popup
                </a>
            </div>
        @endif
    </div>

    <div class="column is-one-third">

        @include('assignment.partials._grademaps_table')
        @include('assignment.partials._deadlines_table')

        <submissions-list :grademaps="grademaps" :charon_id="charonId" :student_id="studentId"
                          v-on:submission-was-activated="showModal">
        </submissions-list>

    </div>

    <submission-modal :submission="activeSubmission" v-on:modal-was-closed="hideModal">
    </submission-modal>
</div>

<script>
    var grademaps = {!! $charon->grademaps->makeHidden('charon_id')->toJson() !!};
    var testerType = "{!! $charon->testerType->name !!}";
    var charonId = {{ $charon->id }};
    var studentId = {{ $student_id }};

    var translations = {
        closeButtonText: "{{ translate('closebuttontitle', 'moodle') }}",
        submissionText: "{{ translate('submission') }}",
        commitMessageText: "{{ translate('commit_message') }}",
        filesText: "{{ translate('files') }}",
        submissionsText: "{{ translate('submissions') }}",
        myRegistrationsText: "{{ translate('my_registrations') }}",
        loadMoreText: "{{ translate('load_more') }}",
        editText: "{{ translate('edit') }}",
        cancelText: "{{ translate('cancel') }}",
        saveText: "{{ translate('save') }}",
        charonPopupText: "{{ translate('charon_popup') }}",
        allRegistrationsText: "{{ translate('all_registrations') }}",
        noRegistrationsText: "{{ translate('no_registrations') }}",
        tableNoRegistrationsText: "{{ translate('table_no_registrations') }}",
        registrationDeletionConfirmationText: "{{ translate('registration_deletion_confirmation') }}",
        registrationBeforeErrorText: "{{ translate('registration_before_error') }}",
        charonText: "{{ translate('charon') }}",
        timeText: "{{ translate('time') }}",
        teacherText: "{{ translate('teacher') }}",
        locationText: "{{ translate('location') }}",
        commentText: "{{ translate('comment') }}",
        actionsText: "{{ translate('actions') }}",
        closeText: "{{ translate('close') }}",
        registrationForText: "{{ translate('registration_for') }}",
        chooseTeacherText: "{{ translate('choose_teacher') }}",
        myTeacherText: "{{ translate('my_teacher') }}",
        anyTeacherText: "{{ translate('any_teacher') }}",
        chooseTimeText: "{{ translate('choose_time') }}",
        selectDayText: "{{ translate('select_day') }}",
        selectTimeText: "{{ translate('select_time') }}",
        registerText: "{{ translate('register') }}"
    };
</script>

@include('partials._highlightjs')
<script src="/mod/charon/plugin/public/js/assignment.js"></script>

{!! $footer !!}
