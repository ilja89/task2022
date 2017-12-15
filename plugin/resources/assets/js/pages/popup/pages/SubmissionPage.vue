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
    import { PageTitle } from '../partials'
    import { SubmissionOverviewSection, OutputSection } from './sections'
    import { Submission, Charon, User } from '../../../models'

    export default {
        components: { PageTitle, SubmissionOverviewSection, OutputSection },

        props: {
            context: { required: true }
        },

        mounted() {
            this.getSubmission()
        },

        watch: {
            $route() {
                if (typeof this.$route.params.submission_id !== 'undefined') {
                    this.getSubmission()
                }
            }
        },

        methods: {
            getSubmission() {
                Submission.findById(this.$route.params.submission_id, submission => {
                    this.context.active_submission = submission

                    if (this.context.active_charon === null) {
                        const charonId = submission.charon_id

                        Charon.all(this.context.course_id, charons => {
                            charons.forEach(charon => {
                                if (charon.id === charonId) {
                                    this.context.active_charon = charon
                                }
                            })
                        })
                    }

                    if (this.context.active_student === null) {
                        const studentId = submission.user_id

                        User.findById(this.context.course_id, studentId, user => {
                            this.context.active_student = user;
                        })
                    }
                })
            }
        }
    }
</script>
