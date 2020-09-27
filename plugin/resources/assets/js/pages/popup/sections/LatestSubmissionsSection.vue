<template>
    <popup-section title="Latest submissions"
                   subtitle="Here are the latest submissions for all tasks in this course">
        <div class="latest-submissions">
            <transition-group name="list">
                <div v-for="(submissionChunk, index) in latestSubmissionsChunks" :key="index" class="columns">
                    <div v-for="submission in submissionChunk" class="column">
                        <div class="card  hover-overlay  submission" @click="submissionSelected(submission)">
                            <div>
                                {{ submission | submissionTime }} <span class="timestamp-separator">|</span>
                                <wbr>
                                {{ formatSubmissionResults(submission) }} <span class="timestamp-separator">|</span>
                                <wbr>
                                {{ submission.charon.name }} <span class="timestamp-separator">|</span>
                                <wbr>
                                {{ submission.user | user }}
                            </div>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>
    </popup-section>
</template>

<script>
    import moment from 'moment'
    import {mapGetters, mapActions} from 'vuex'
    import {PopupSection} from '../layouts/index'
    import {Submission} from '../../../api/index'
    import {formatName, formatSubmissionResults} from '../helpers/formatting'

    export default {
        name: "latest-submissions-section",

        components: {PopupSection},

        data() {
            return {
                latestSubmissions: [],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
                'submissionLink',
            ]),

            latestSubmissionsChunks() {
                const chunkSize = 2

                let chunks = []
                let chunk = []
                this.latestSubmissions.forEach(submission => {
                    if (chunk.length < chunkSize) {
                        chunk.push(submission)
                    } else {
                        chunks.push(chunk)
                        chunk = [submission]
                    }
                })

                if (chunk.length) {
                    chunks.push(chunk)
                }

                return chunks
            },
        },

        filters: {
            user(user) {
                return formatName(user)
            },

            submissionTime(submission) {
                const time = moment(submission.created_at)

                return time.format('D MMM HH:mm')
            },
        },

        methods: {
            ...mapActions([
                'fetchStudent',
            ]),

            fetchLatestSubmissions() {
                Submission.findLatest(this.courseId, submissions => {
                    this.latestSubmissions = submissions
                })
            },

            submissionSelected(submission) {
                this.$router.push(this.submissionLink(submission.id))
            },

            formatSubmissionResults(submission) {
                return formatSubmissionResults(submission, ', ');
            },
        },

        created() {
            this.fetchLatestSubmissions()
            VueEvent.$on('refresh-page', this.fetchLatestSubmissions)
        },

        beforeDestroy() {
            VueEvent.$off('refresh-page', this.fetchLatestSubmissions)
        },
    }
</script>

<style lang="scss" scoped>

    @import '../../../../../../../node_modules/bulma/sass/utilities/all';

    .submission {
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 30px;
        padding-bottom: 30px;

        white-space: nowrap;
        line-height: 1.5rem;

        @include touch {
            padding-bottom: 20px;
            padding-left: 10px;
        }
    }

    .timestamp-separator {
        padding-left: 4px;
        padding-right: 4px;
    }

</style>