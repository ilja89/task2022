@include('popup.header')

<div id="app">

    <popup-header
            :context="context">
    </popup-header>

    <popup-navigation>

        <popup-page name="Grading" :selected="true">

            <grading-page v-if="context.active_student !== null"
                    :context="context">
            </grading-page>

            <no-student-selected-page v-else></no-student-selected-page>

        </popup-page>

        <popup-page name="Submission">

            <submission-page v-if="context.active_student !== null"
                    :context="context"></submission-page>

            <no-student-selected-page v-else></no-student-selected-page>

        </popup-page>

        <popup-page name="Student overview">

            <no-student-selected-page></no-student-selected-page>

        </popup-page>

    </popup-navigation>

    <loader :visible="loaderVisible !== 0"></loader>
    <notification :text="notification_text" :show="notification_show"></notification>
</div>

<script>
    window.course_id = {!! $course->id !!};
</script>

@include('popup.footer')
