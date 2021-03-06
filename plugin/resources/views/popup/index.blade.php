@include('popup.header')

<div id="app">
    <popup></popup>
</div>

<script>
    window.course_id = {!! $course->id !!};
    window.course_name = '{!! $course->fullname !!}';
    window.course_shortname = '{!! $course->shortname !!}';
</script>

@include('popup.footer')
