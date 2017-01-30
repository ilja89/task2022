<template>
    <div class="page-container">
        <page-title
                :student="context.active_student">
        </page-title>

        <popup-section
                title="Pick a task"
                subtitle="Here are the submissions from one student for the given task.">

            <template slot="header-right">
                <charon-select
                        :charons="context.charons">
                </charon-select>
            </template>

            <h1>submissions here!</h1>

        </popup-section>

    </div>
</template>

<script>
    import PageTitle from './partials/PageTitle.vue';
    import PopupSection from './partials/PopupSection.vue';
    import CharonSelect from './partials/CharonSelect.vue';

    export default {
        components: { PageTitle, PopupSection, CharonSelect },

        props: {
            context: { required: true }
        }
    }
</script>
