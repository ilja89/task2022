<template>

    <div class="page-container">

        <page-title :student="context.active_student"></page-title>

        <submission-overview-section :context="context" :submission="context.active_submission"></submission-overview-section>

        <output-section :context="context"></output-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import SubmissionOverviewSection from './sections/SubmissionOverviewSection.vue';
    import OutputSection from './sections/OutputSection.vue';

    export default {
        components: { PageTitle, SubmissionOverviewSection, OutputSection },

        props: {
            context: { required: true }
        }
    }
</script>
