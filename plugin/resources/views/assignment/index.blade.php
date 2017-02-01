{!! $header !!}

<link rel="stylesheet" href="/mod/charon/plugin/public/external/highlight/styles/default.css">
<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1>{{ $charon->name }}</h1>

{!! $charon->description !!}

<script src="/mod/charon/plugin/public/external/highlight/highlight.pack.js"></script>
<script>hljs.initHighlightingOnLoad();</script>
{{--<script src="/mod/charon/plugin/public/js/highlight.js"></script>--}}
{{--<script src="/mod/charon/plugin/public/js/assignment.js"></script>--}}

{!! $footer !!}
