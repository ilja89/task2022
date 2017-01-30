<template>

    <div class="page-container">

        <page-title :student="context.active_student"></page-title>

        <h1 class="title is-1">Hello World</h1>

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
