<template>
    <div class="submissions">
Total points: {{ totalCharonPoints }}
        <h3 v-show="submissions.length === 0" class="title is-3">
            No submissions found!
        </h3>

        <transition-group name="list">
            <submission-partial
                    v-for="submission in orderedSubmissions"
                    :key="submission.id"
                    :submission="submission"
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
    import {mapState, mapGetters} from 'vuex'
    import _ from 'lodash';
    import SubmissionPartial from './Submission'
    import {Charon, Submission} from '../../../api'

    export default {

        components: {SubmissionPartial},

        data() {
            return {
                canLoadMore: true,
                submissions: [],
                totalCharonPoints: null
            }
        },

        computed: {
            ...mapGetters([
                'submissionLink',
            ]),

            ...mapState([
                'charon',
                'student',
            ]),
            orderedSubmissions: function () {
                return _.orderBy(this.submissions, 'confirmed', 'desc')
            },
        },

        methods: {

            refreshSubmissions() {
                if (this.student == null || this.charon == null || this._inactive) {
                    return
                }

                Submission.findByUserCharon(this.student.id, this.charon.id, submissions => {
                    this.submissions = submissions
                    this.canLoadMore = Submission.canLoadMore()

                    if (this.submissions) {
                        Charon.getResultForStudent(
                            this.charon.id,
                            this.submissions[0].user_id,
                            points => {
                                this.totalCharonPoints = points;
                            }
                        );
                    }
                })
            },

            onSubmissionSelected(submission) {
                this.$router.push(this.submissionLink(submission.id))
            },

            loadMoreSubmissions() {
                if (Submission.canLoadMore()) {
                    Submission.getNext(submissions => {
                        submissions.forEach(submission => this.submissions.push(submission))
                        this.canLoadMore = Submission.canLoadMore()
                    })
                } else {
                    this.canLoadMore = false
                }
            },
        },

        watch: {
            charon() {
                this.refreshSubmissions()
            },

            student() {
                this.refreshSubmissions()
            },
        },

        created() {
            this.refreshSubmissions()
            VueEvent.$on('refresh-page', this.refreshSubmissions)
        },

        beforeDestroy() {
            VueEvent.$off('refresh-page', this.refreshSubmissions)
        },
    }
</script>
