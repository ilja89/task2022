@include('popup.header')

<div id="app">
    <popup></popup>
</div>

<script>
    window.course_id = {!! $course->id !!};
</script>

@include('popup.footer')
