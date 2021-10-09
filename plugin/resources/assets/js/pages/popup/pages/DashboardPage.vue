<template>
    <div>

        <page-title :title="this.title"></page-title>

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
        data() {
          return {
            title: `${'Charon dashboard - ' + window.course_name}`,
          }
        },

        computed: {
            ...mapState([
                'is_mobile'
            ]),
        },

        components: {
            PageTitle, LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection,
            StudentsDistributionSection, LogSection
        },

        metaInfo() {
            return {
              title: `${'Charon dashboard - ' + window.course_name}`
            }
        },
    }
</script>
