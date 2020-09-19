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
    import store from './../store/index'
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
                if (store.state.is_mobile) {
                    return `Dashboard - ${window.course_name}`
                } else {
                    return "Dashboard"
                }
            },
        },

        components: {
            PageTitle, LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection,
            StudentsDistributionSection, LogSection
        },
    }
</script>
