<template>
    <div class="page-container">
        <page-title
                :student="context.active_student">
        </page-title>
    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';

    export default {
        components: { PageTitle },

        props: {
            context: { required: true }
        }
    }
</script>
