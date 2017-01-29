<template>
    <div class="page-container">
        <page-title
                :student="context.active_student">
        </page-title>

        <popup-section
                title="Pick a task"
                subtitle="Here are the submissions from one student for the given task.">

            <template slot="header-right">
                <button>boom</button>
            </template>

            <h1>submissions here!</h1>

        </popup-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import PopupSection from './partials/PopupSection.vue';

    export default {
        components: { PageTitle, PopupSection },

        props: {
            context: { required: true }
        }
    }
</script>
