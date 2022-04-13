<template>
    <div>

        <page-title :title="this.title"></page-title>

        <latest-submissions-section></latest-submissions-section>

        <!--        <active-students-section></active-students-section>-->

        <submission-counts-section></submission-counts-section>

        <students-distribution-section></students-distribution-section>

        <log-section></log-section>

        <log-section :title="query_logs_title" :subtitle="query_logs_subtitle" :query-log-type="true"></log-section>

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
        data() {
            return {
                title: `${'Charon dashboard - ' + window.course_name}`,
            }
        },

        computed: {
            ...mapState([
                'is_mobile'
            ]),

            page_name() {
                return `Dashboard - ${window.course_name}`
            },

            query_logs_title() {
                return 'Query logs'
            },

            query_logs_subtitle() {
                return 'Here are some recent query logs'
            }
        },

        components: {
            PageTitle, LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection,
            StudentsDistributionSection, LogSection
        },

        metaInfo() {
            return {
                title: this.title
            }
        },
    }
</script>
