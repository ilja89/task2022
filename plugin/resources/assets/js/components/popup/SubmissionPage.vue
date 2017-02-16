<template>

    <div>

        <page-title :student="context.active_student"></page-title>

        <submission-overview-section :charon="context.active_charon" :submission="context.active_submission">
        </submission-overview-section>

        <output-section :submission="context.active_submission" :charon="context.active_charon"></output-section>

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
