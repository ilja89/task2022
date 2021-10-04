@include('popup.header')

<link href="/mod/charon/plugin/public/css/ansi2html_style.css" rel="stylesheet">
<div id="app">
    <popup></popup>
</div>

<script>
    window.course_id = {!! $course->id !!};
    window.course_name = '{!! $course->fullname !!}';
    window.course_shortname = '{!! $course->shortname !!}';
</script>

@include('popup.footer')
