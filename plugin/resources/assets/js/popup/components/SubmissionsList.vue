<template>

    <div class="submissions">

        <div v-show="submissions.length === 0">
            <h3 class="title is-3">No submissions found!</h3>
        </div>

        <submission-partial v-for="submission in submissions"
                            :submission="submission"
                            @submission-was-selected="onSubmissionSelected(submission)">
        </submission-partial>

        <div v-if="canLoadMore" class="has-text-centered">
            <button class="button is-primary" @click="loadMoreSubmissions()">
                Load more
            </button>
        </div>

    </div>

</template>

<script>
    import SubmissionPartial from '../partials/Submission.vue';
    import Submission from '../../models/Submission';

    export default {

        components: { SubmissionPartial },

        props: {
            charon: { required: true },
            student: { required: true },
            active_submission: { required: true }
        },

        data() {
            return {
                submissions: [],
                canLoadMore: true
            };
        },

        mounted() {
            this.refreshSubmissions();
            VueEvent.$on('refresh-page', () => this.refreshSubmissions());
        },

        watch: {
            charon() {
                this.refreshSubmissions();
            },

            student() {
                this.refreshSubmissions();
            }
        },

        methods: {

            refreshSubmissions() {
                if (this.student === null || this.charon === null) {
                    return;
                }

                Submission.findByUserCharon(this.student.id, this.charon.id, (submissions) => {
                    this.submissions = submissions;
                    this.canLoadMore = true;

                    submissions.forEach(submission => {
                        if (this.active_submission !== null && this.active_submission.id == submission.id) {
                            this.emitSubmissionChange(submission);
                        }
                    });

                    if (this.active_submission === null &&  this.submissions.length > 0) {
                        this.emitSubmissionChange(this.submissions[0]);
                    }
                });
            },

            onSubmissionSelected(submission) {
                this.emitSubmissionChange(submission);
                this.$router.push('/submission/' + submission.id)
            },

            emitSubmissionChange(submission) {
                VueEvent.$emit('submission-was-selected', submission);
            },

            loadMoreSubmissions() {
                if (Submission.nextUrl !== null) {
                    Submission.getNext(submissions => {
                        submissions.forEach(submission => this.submissions.push(submission));
                    });
                } else {
                    this.canLoadMore = false;
                }
            },
        }
    }
</script>
