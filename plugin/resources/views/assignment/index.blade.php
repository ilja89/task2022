{!! $header !!}

<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">
<link href="/mod/charon/plugin/public/css/instanceForm.css" rel="stylesheet">
<h1 class="title">{{ $charon->name }}</h1>

<div class="rows assignment-rows" id="app">
    <div class="columns assignment-columns">
        <div class="column is-two-thirds assignment-content content" v-highlightjs>

            {!! rewritePluginTextUrls($charon->description, 'description', $course_module_id) !!}

            <div class="edit-container" v-if="!(allow_submission > 0)">
                <code-templates
                        :language="language"
                        :allow_submission="allow_submission">
                </code-templates><br>
            </div>

        </div>

        <div class="column is-one-third">

            @include('assignment.partials._grademaps_table')
            @include('assignment.partials._deadlines_table')
            <div v-if="!(allow_submission > 0)">
                <h2 class="title">{{ translate('submissions') }}</h2>

                <assignment-view
                        :allow_submission="allow_submission">
                </assignment-view>
            </div>

        </div>
    </div>
    <div class="columns assignment-columns">
        <div class="column is-two-thirds assignment-content content" v-highlightjs>
            <div class="edit-container" v-if="allow_submission > 0">
                <code-templates
                        :language="language"
                        :allow_submission="allow_submission">
                </code-templates><br>
            </div>
            @if ($can_edit)
                <div class="edit-container">
                    <a class="button is-link" href="/mod/charon/courses/{{$charon->course}}/popup">
                        Charon popup
                    </a>

                    <a class="button is-link" href="/course/modedit.php?update={{ $charon->courseModule()->id }}&return=1&sr=0">
                        Grade settings
                    </a>

                    <a class="button is-link" href="/mod/charon/courses/{{$charon->course}}/popup#/charonSettings/{{$charon->id}}">
                        Registration settings
                    </a>
                </div>
            @endif
        </div>
        <div class="column is-one-third">
            <div v-if="allow_submission > 0">
                <h2 class="title">{{ translate('submissions') }}</h2>

                <assignment-view
                        :allow_submission="allow_submission">
                </assignment-view>
            </div>
        </div>
    </div>
</div>

<script>
    var grademaps = {!! $charon->grademaps->makeHidden('charon_id')->toJson() !!};
    var testerType = "{!! $charon->testerType->name !!}";
    var charonId = {{ $charon->id }};
    var studentId = {{ $student_id }};

    window.allow_submission = {{ $charon->allow_submission }};
    window.language = "{!! $charon->testerType->name !!}";

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
        registrationDeferConfirmationText: "{{translate('ask_registration_defer')}}",
        registrationBeforeErrorText: "{{ translate('registration_before_error') }}",
        charonText: "{{ translate('charon') }}",
        labNameText: "{{ translate('lab_name') }}",
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
        registerText: "{{ translate('register') }}",
        progressText: "{{ translate('progress') }}",
        programmingLanguage: "{{ translate('language') }}",
        copyButton: "{{ translate('copy') }}",
        submitButton: "{{ translate('submit') }}",
        resetToTemplates: "{{ translate('reset_to_templates') }}",
        progressText: "{{ translate('progress') }}",
        successfulRegistrationDeferText: "{{ translate('successful_registration_defer') }}",
        failedRegistrationDeferText: "{{translate('failed_registration_defer')}}"
    };
</script>

@include('partials._highlightjs')
<script src="/mod/charon/plugin/public/js/assignment.js"></script>

{!! $footer !!}
