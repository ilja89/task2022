{!! $header !!}

<link href="/mod/charon/plugin/public/{{ elixir('css/assignment.css') }}" rel="stylesheet">

<h1>This is the assignment view!</h1>

<p>ID: {{ $instance->id }}</p>
<p>Name: {{ $instance->name }}</p>
<p>Description: {!! $instance->description !!}</p>

{!! $footer !!}
