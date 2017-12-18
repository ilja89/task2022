<template>
    <popup-section
            title="Latest submissions"
            subtitle="Here are the latest submissions for all tasks in this course"
    >
        <div class="latest-submissions">
            <transition-group name="list">
                <div
                        v-for="(submissionChunk, index) in latestSubmissionsChunks"
                        :key="index"
                        class="columns"
                >
                    <div
                            v-for="submission in submissionChunk"
                            class="column"
                    >
                        <div
                                class="card  hover-overlay  submission"
                                @click="submissionSelected(submission)"
                        >
                            <div>
                                {{ submission | submissionTime }} <span class="timestamp-separator">|</span>
                                <wbr>{{ submission.charon.name }} <span class="timestamp-separator">|</span>
                                <wbr>{{ submission.user | user }}
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
    import { mapGetters } from 'vuex'
    import { PopupSection } from '../../layouts'
    import { Submission } from '../../../../models'
    import { formatName } from '../../helpers/formatting'

    export default {
        name: "latest-submissions-section",

        components: { PopupSection },

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
                const time = moment(submission.created_at.date)

                return time.format('D MMM HH:mm')
            },
        },

        methods: {
            fetchLatestSubmissions() {
                Submission.findLatest(this.courseId, submissions => {
                    this.latestSubmissions = submissions
                })
            },

            submissionSelected(submission) {
                this.$router.push(this.submissionLink(submission.id))
            },
        },

        mounted() {
            this.fetchLatestSubmissions()
            VueEvent.$on('refresh-page', this.fetchLatestSubmissions);
        },
    }
</script>

<style lang="scss" scoped>

    @import '~bulma/sass/utilities/_all';

    .submission {
        margin-top:    0;
        margin-bottom: 0;

        white-space: nowrap;

        @include touch {
            padding-bottom: 20px;
            padding-left: 10px;
        }
    }

    .timestamp-separator {
        padding-left:  4px;
        padding-right: 4px;
    }

</style>