<template>
    <div>

        <page-title :student="context.active_student"></page-title>

        <submissions-section :context="context"></submissions-section>

        <comments-section :charon="context.active_charon" :student="context.active_student"></comments-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import SubmissionsSection from './sections/SubmissionsSection.vue';
    import CommentsSection from './sections/CommentsSection.vue';

    export default {
        components: { PageTitle, SubmissionsSection, CommentsSection },

        props: {
            context: { required: true }
        }
    }
</script>
