<template>
    <div class="page-container">

        <page-title :student="context.active_student"></page-title>

        <submissions-section :context="context"></submissions-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import SubmissionsSection from './sections/SubmissionsSection.vue';

    export default {
        components: { PageTitle, SubmissionsSection },

        props: {
            context: { required: true }
        }
    }
</script>
