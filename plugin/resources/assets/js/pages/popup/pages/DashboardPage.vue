<template>
    <div>

        <page-title title="Dashboard"></page-title>

        <latest-submissions-section></latest-submissions-section>

        <active-students-section></active-students-section>

        <submission-counts-section></submission-counts-section>

        <students-distribution-section></students-distribution-section>

    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import {
        LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection, StudentsDistributionSection,
    } from '../sections'

    export default {
        name: "dashboard-page",

        components: {
            PageTitle, LatestSubmissionsSection, ActiveStudentsSection, SubmissionCountsSection,
            StudentsDistributionSection,
        },
    }
</script>
