{!! $header !!}

<link href="/mod/charon/plugin/public/css/assignment.css" rel="stylesheet">

<h1>This is the assignment view!</h1>

<p>ID: {{ $charon->id }}</p>
<p>Name: {{ $charon->name }}</p>
<p>Project folder: {{ $charon->project_folder }}</p>
<p>Extra: {{ $charon->extra }}</p>
<p>Tester type: {{ $charon->testerType->name }}</p>
<p>Grading method: {{ $charon->gradingMethod->name }}</p>
<p>Description: {!! $charon->description !!}</p>
<p>Created at: {{ $charon->created_at }}</p>
<p>Updated at: {{ $charon->updated_at }}</p>

{!! $footer !!}
