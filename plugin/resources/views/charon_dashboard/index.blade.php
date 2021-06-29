@include('charon_dashboard.header')

<div id="app">
    <charon-dashboard></charon-dashboard>
</div>

<script>
    window.course_id = {!! $course->id !!};
    window.charon_id = {!! $charon->id !!};
    window.course_name = '{!! $course->fullname !!}';
    window.course_shortname = '{!! $course->shortname !!}';
</script>

@include('charon_dashboard.footer')