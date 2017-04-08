{!! $header !!}

<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1 class="title">{{ $charon->name }}</h1>

<div class="columns" id="app">

    <div class="column assignment-content content">

        {!! rewritePluginIntroUrls($charon->description, $charon->course) !!}

        @if ($can_edit)
            <div class="edit-container">
                <a class="button is-link" href="/course/modedit.php?update={{ $charon->courseModule()->id }}&return=1&sr=0">
                    Edit
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
    };
</script>

@include('partials._highlightjs')
<script src="/mod/charon/plugin/public/js/assignment.js"></script>

{!! $footer !!}
