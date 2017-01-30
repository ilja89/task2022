<template>

    <div class="page-container">

        <page-title :student="context.active_student"></page-title>

        <submission-overview-section :context="context">
        </submission-overview-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import SubmissionOverviewSection from './sections/SubmissionOverviewSection.vue';

    export default {
        components: { PageTitle, SubmissionOverviewSection },

        props: {
            context: { required: true }
        }
    }
</script>
