@include('popup.header')

<div id="app">

    <popup-header
            :context="context">
    </popup-header>

    <popup-navigation></popup-navigation>

    <popup-content></popup-content>

    <loader :visible="false"></loader>
</div>

<script>
    window.course_id = {!! $course->id !!};
</script>

<script src="/mod/charon/plugin/public/js/popup.js"></script>

@include('popup.footer')
