<template>
    <div>

        <page-title :title="page_name"></page-title>

        <latest-submissions-section></latest-submissions-section>

        <!--        <active-students-section></active-students-section>-->

        <submission-counts-section></submission-counts-section>

        <students-distribution-section></students-distribution-section>

        <log-section></log-section>

    </div>
</template>

<script>
    import {mapState} from "vuex";
    import {PageTitle} from '../partials'
    import {
        LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection, StudentsDistributionSection,
        LogSection
    } from '../sections'

    export default {
        name: "dashboard-page",

        computed: {
            ...mapState([
                'is_mobile'
            ]),

            page_name() {
                return `Dashboard - ${window.course_name}`
            },
        },

        components: {
            PageTitle, LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection,
            StudentsDistributionSection, LogSection
        },
    }
</script>
