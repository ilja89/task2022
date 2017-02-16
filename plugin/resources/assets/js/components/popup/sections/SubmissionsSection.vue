<template>

    <popup-section
            title="Pick a task"
            subtitle="Here are the submissions from one student for the given task.">

        <template slot="header-right">
            <charon-select
                    :charons="context.charons">
            </charon-select>
        </template>

        <submissions-list
                :charon="context.active_charon"
                :student="context.active_student"
                :active_submission="context.active_submission">
        </submissions-list>

    </popup-section>

</template>

<script>
    import PopupSection from '../partials/PopupSection.vue';
    import CharonSelect from '../partials/CharonSelect.vue';
    import SubmissionsList from '../partials/SubmissionsList.vue';

    export default {
        components: { PopupSection, CharonSelect, SubmissionsList },

        props: {
            context: { required: true }
        }
    }
</script>
