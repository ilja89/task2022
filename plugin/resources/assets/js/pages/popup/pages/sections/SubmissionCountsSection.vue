<template>
    <popup-section
            title="Submission counts"
            subtitle="Submission counts for Charons."
    >
        <div class="card has-padding">
            <table class="table  submissions-count__table">
                <thead>
                <tr>
                    <th>Charon</th>
                    <th>Different users</th>
                    <th>Total submissions</th>
                    <th>Submissions per user</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="charon in sortedCounts">
                    <td>{{ charon.project_folder }}</td>
                    <td>{{ charon.diff_users }}</td>
                    <td>{{ charon.tot_subs }}</td>
                    <td>{{ charon.subs_per_user ? charon.subs_per_user : 0 }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </popup-section>
</template>

<script>
    import { mapGetters } from 'vuex'
    import { Submission } from '../../../../models'
    import { PopupSection } from '../../layouts'

    export default {
        name: "submission-counts-section",

        components: { PopupSection },

        data() {
            return {
                submissionCounts: [],
                sorted: ['project_folder', 'desc'],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            sortedCounts() {
                return this.submissionCounts
            },
        },

        mounted() {
            this.fetchSubmissionCounts()
        },

        methods: {
            fetchSubmissionCounts() {
                Submission.findSubmissionCounts(this.courseId, counts => {
                    this.submissionCounts = counts
                })
            },
        },
    }
</script>

<style lang="scss" scoped>

    .submissions-count__table {
        width: 100%;
    }

</style>