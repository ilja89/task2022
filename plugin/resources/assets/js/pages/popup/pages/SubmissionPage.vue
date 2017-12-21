<template>
    <div>

        <page-title :student="student"></page-title>

        <submission-overview-section/>

        <output-section/>

    </div>
</template>

<script>
    import { mapState, mapActions, mapGetters } from 'vuex'

    import { PageTitle } from '../partials'
    import { SubmissionOverviewSection, OutputSection } from '../sections'
    import { Submission, Charon, User } from '../../../api'

    export default {
        components: { PageTitle, SubmissionOverviewSection, OutputSection },

        computed: {
            ...mapState([
                'student',
                'charon',
            ]),

            ...mapGetters([
                'courseId',
            ]),
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

            ...mapActions([
                'fetchStudent',
                'updateCharon',
                'updateSubmission',
            ]),

            getSubmission() {
                Submission.findById(this.$route.params.submission_id, submission => {
                    this.updateSubmission({ submission })

                    if (this.charon === null) {
                        const charonId = submission.charon_id

                        Charon.all(this.courseId, charons => {
                            charons.forEach(charon => {
                                if (charon.id === charonId) {
                                    this.updateCharon({ charon })
                                }
                            })
                        })
                    }

                    if (this.student === null) {
                        const studentId = submission.user_id
                        const courseId = this.courseId

                        this.fetchStudent({ studentId, courseId })
                    }
                })
            }
        }
    }
</script>
