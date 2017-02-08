{!! $header !!}

<link rel="stylesheet" href="/mod/charon/plugin/public/external/highlight/styles/default.css">
<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1 class="title">{{ $charon->name }}</h1>

<div class="columns">
    <div class="column assignment-content">
        {!! $charon->description !!}
    </div>

    <div class="column is-one-quarter">

        <h2 class="title">Deadlines</h2>

        <table class="table is-bordered">
            <thead>
                <tr>
                    <th>Deadline</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($charon->deadlines as $deadline)
                    <tr>
                        <td>{{ $deadline->deadline_time }}</td>
                        <td>{{ $deadline->percentage }}%</td>
                    </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</div>

<script src="/mod/charon/plugin/public/external/highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>

{!! $footer !!}
