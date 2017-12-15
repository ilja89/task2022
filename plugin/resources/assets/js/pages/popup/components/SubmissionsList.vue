<template>

    <div class="submissions">

        <div v-show="submissions.length === 0">
            <h3 class="title is-3">No submissions found!</h3>
        </div>

        <transition-group name="list">
            <submission-partial
                    v-for="submission in submissions"
                    :submission="submission"
                    :key="submission.id"
                    @submission-was-selected="onSubmissionSelected(submission)"
            >
            </submission-partial>
        </transition-group>

        <div v-if="canLoadMore && submissions.length > 0" class="has-text-centered">
            <button class="button is-primary" @click="loadMoreSubmissions()">
                Load more
            </button>
        </div>

    </div>

</template>

<script>
    import { mapGetters } from 'vuex'
    import { Submission as SubmissionPartial } from '../partials';
    import { Submission } from '../../../models';

    export default {

        components: { SubmissionPartial },

        props: {
            active_submission: { required: true },
            charon: { required: true },
            student: { required: true },
        },

        data() {
            return {
                canLoadMore: true,
                submissions: [],
            };
        },

        computed: {
            ...mapGetters([
                'submissionLink',
           ]),
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
                    this.canLoadMore = Submission.canLoadMore();
                });
            },

            onSubmissionSelected(submission) {
                this.$router.push(this.submissionLink(submission.id))
            },

            loadMoreSubmissions() {
                if (Submission.canLoadMore()) {
                    Submission.getNext(submissions => {
                        submissions.forEach(submission => this.submissions.push(submission));
                        this.canLoadMore = Submission.canLoadMore();
                    });
                } else {
                    this.canLoadMore = false;
                }
            },
        },

        mounted() {
            this.refreshSubmissions();
            VueEvent.$on('refresh-page', () => this.refreshSubmissions());
        },
    }
</script>
