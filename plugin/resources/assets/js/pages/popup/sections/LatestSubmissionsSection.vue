<template>
    <popup-section title="Latest submissions"
                   subtitle="Here are the latest submissions for all tasks in this course">
        <div class="latest-submissions">

            <transition-group name="list">
                <div v-for="submissionChunk in submissionChunks" :key="submissionChunk.id" class="columns">
                    <div v-for="submission in submissionChunk.subs" class="column">
                        <div class="card  hover-overlay  submission" @click="submissionSelected(submission)">
                            <v-badge :value="submission.review_comments.length"
                                     :content="submission.review_comments.length < 10 ? submission.review_comments.length : '9+'"
                                     overlap
                                     left
                                     offset-x="-1"
                            >
                                <div>
                                    <span class="submission-line">
                                    {{ submission | submissionTime }}
                                    <span class="timestamp-separator">|</span>
                                </span><span class="submission-line">
                                    {{ submission.charon.name }}
                                    <span class="timestamp-separator">|</span>
                                </span><span class="submission-line">
                                    {{ formatResults(submission) }}
                                </span>
                                </div>
                            </v-badge>
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
import {latestSubmissionsChunks, formatStudentResults} from "../helpers/helpers";

export default {
    name: "latest-submissions-section",

    components: {PopupSection},

    data() {
        return {
            latestSubmissions: [],
        }
    },

    props: {
        isCharonDashboard: {
            required: false,
            default: false,
            type: Boolean
        },

        charonLatestSubmissions: {
            required: false,
            default() {
                return []
            },
            type: Array
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
            'submissionLink',
        ]),

        submissionChunks() {
            return this.isCharonDashboard ? latestSubmissionsChunks(this.charonLatestSubmissions) : latestSubmissionsChunks(this.latestSubmissions)
        },
    },

    filters: {
        submissionTime(submission) {
            return moment(submission.created_at).format('D MMM HH:mm')
        },
    },

    methods: {
        ...mapActions([
            'fetchStudent',
        ]),

        fetchLatestSubmissions() {
            if (!this.isCharonDashboard) {
                Submission.findLatest(this.courseId, submissions => {
                    this.latestSubmissions = submissions
                })
            }
        },

        submissionSelected(submission) {
            this.$router.push(this.submissionLink(submission.id))
        },

        formatResults(submission) {
            return formatStudentResults(submission)
        }
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
    padding-top: 1em;
    padding-bottom: 1em;
    display: inherit;

    word-break: break-word;
    line-height: 1.5rem;

    @include touch {
        padding-bottom: 1em;
        padding-left: 1em;
        padding-right: 1em;
    }
}

.submission-line {
    display: inline-block;
    padding-top: 0.5em;
}

.timestamp-separator {
    padding-left: 0.2em;
    padding-right: 0.2em;
}

.div.card.hover-overlay.submission {
    padding-top: 1em;
    padding-bottom: 1em;
    padding-right: 1em;
}

</style>
