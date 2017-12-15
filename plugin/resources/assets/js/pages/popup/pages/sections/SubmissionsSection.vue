<template>

    <popup-section
            title="Pick a task"
            subtitle="Here are the submissions from one student for the given task.">

        <template slot="header-right">
            <charon-select
                    :active_charon="charon"
                    @charon-was-changed="onCharonChanged">
            </charon-select>
        </template>

        <submissions-list
                :charon="charon"
                :student="student"
                :active_submission="submission">
        </submissions-list>

    </popup-section>

</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import { PopupSection } from '../../layouts';
    import { CharonSelect, SubmissionsList } from '../../components';

    export default {
        components: { PopupSection, CharonSelect, SubmissionsList },

        computed: {
            ...mapState([
                'student',
                'charon',
                'submission',
            ]),
        },

        methods: {
            ...mapActions([
                'updateCharon',
                'updateSubmission',
            ]),

            onCharonChanged(charon) {
                this.updateCharon({ charon })
                this.updateSubmission({ submission: null })
            },
        },
    }
</script>
