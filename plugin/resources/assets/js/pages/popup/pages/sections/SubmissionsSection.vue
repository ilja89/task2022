<template>

    <popup-section
            title="Pick a task"
            subtitle="Here are the submissions from one student for the given task.">

        <template slot="header-right">
            <charon-select
                    :active_charon="context.active_charon"
                    @charon-was-changed="onCharonChanged">
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
    import PopupSection from '../../layouts/PopupSection.vue';
    import CharonSelect from '../../components/CharonSelect.vue';
    import SubmissionsList from '../../components/SubmissionsList.vue';

    export default {
        components: { PopupSection, CharonSelect, SubmissionsList },

        props: {
            context: { required: true }
        },

        methods: {
            onCharonChanged(charon) {
                this.context.active_charon = charon;
                this.context.active_submission = null;
            },
        }
    }
</script>
