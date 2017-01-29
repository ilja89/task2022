@include('popup.header')

<div id="app">

    <popup-header
            :context="context">
    </popup-header>

    <popup-navigation>

        <popup-page
                name="Grading"
                :selected="true">
            <h1>Hello World Grading</h1>
        </popup-page>

        <popup-page
                name="Submission">
            <h1>Hello World Submission</h1>
        </popup-page>

        <popup-page
                name="Student overview">
            <h1>Hello World Student Overview</h1>
        </popup-page>

    </popup-navigation>

    <loader :visible="false"></loader>
</div>

<script>
    window.course_id = {!! $course->id !!};
</script>

<script src="/mod/charon/plugin/public/js/popup.js"></script>

@include('popup.footer')
