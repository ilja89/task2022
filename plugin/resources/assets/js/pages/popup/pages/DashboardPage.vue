<template>
    <div>

        <page-title title="Dashboard"></page-title>

        <popup-section
                title="Latest submissions"
                subtitle="Here are the latest submissions for all tasks in this course"
        >
            <div class="card">
                <ul>
                    <li v-for="submission in latestSubmissions">
                        <router-link :to="submissionLink(submission.id)">
                            by {{ submission.user | user }}
                        </router-link>
                    </li>
                </ul>
            </div>
        </popup-section>

    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import { PageTitle } from '../partials'
    import { Submission } from '../../../models'
    import { PopupSection } from '../layouts'

    export default {
        name: "dashboard-page",

        components: { PageTitle, PopupSection },

        data() {
            return {
                latestSubmissions: [],
                charonSubmissionCounts: [],
                activeUsers: [],
                usersByTotalPoints: [],
            }
        },

        computed: {
            ...mapState([
                'course',
            ]),

            ...mapGetters([
                'courseId',
                'submissionLink',
            ]),
        },

        filters: {
            user(user) {
                return `${user.firstname} ${user.lastname} (${user.idnumber})`
            },
        },

        methods: {
            fetchLatestSubmissions() {
                Submission.findLatest(this.courseId, submissions => {
                    this.latestSubmissions = submissions
                })
            },

            fetchCharonSubmissionCounts() {

            },

            fetchActiveUsers() {

            },

            fetchUsersByTotalPoints() {

            },
        },

        mounted() {
            this.fetchLatestSubmissions()
            this.fetchCharonSubmissionCounts()
            this.fetchActiveUsers()
            this.fetchUsersByTotalPoints()
        },
    }
</script>

<style lang="scss" scoped>

</style>