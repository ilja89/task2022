<template>
    <div>

        <page-title :student="context.active_student"></page-title>

        <submission-overview-section
                :charon="context.active_charon"
                :submission="context.active_submission"
        >
        </submission-overview-section>

        <output-section :submission="context.active_submission" :charon="context.active_charon"></output-section>

    </div>
</template>

<script>
    import { PageTitle } from '../partials';
    import { SubmissionOverviewSection, OutputSection } from './sections';
    import { Submission } from '../../../models';

    export default {
        components: { PageTitle, SubmissionOverviewSection, OutputSection },

        props: {
            context: { required: true }
        },

        mounted() {
            this.getSubmission();
        },

        watch: {
            $route() {
                if (typeof this.$route.params.submission_id !== 'undefined') {
                    this.getSubmission();
                }
            }
        },

        methods: {
            getSubmission() {
                if (this.context.active_charon === null) {
                    return null;
                }

                Submission.findById(this.context.active_charon.id, this.$route.params.submission_id, submission => {
                    this.context.active_submission = submission;
                });
            }
        }
    }
</script>
