{!! $header !!}

<link rel="stylesheet" href="/mod/charon/plugin/public/external/highlight/styles/default.css">
<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1 class="title">{{ $charon->name }}</h1>

<div class="columns" id="app">

    <div class="column assignment-content content">
        {!! $charon->description !!}

        @if ($can_edit)
            <div class="edit-container">
                <a class="button is-link" href="/course/modedit.php?update={{ $charon->courseModule()->id }}&return=1&sr=0">
                    Edit
                </a>
            </div>
        @endif
    </div>

    <div class="column is-one-third">

        @include('assignment.partials._deadlines_table')

        <submissions-list :submissions="submissions" :grademaps="grademaps"></submissions-list>

    </div>
</div>

<script>
    var grademaps = {!! $charon->grademaps->makeHidden('charon_id')->toJson() !!};
    var submissions = {!! $submissions->toJson() !!};
</script>

<script src="/mod/charon/plugin/public/external/highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
<script src="/mod/charon/plugin/public/js/assignment.js"></script>

{!! $footer !!}
