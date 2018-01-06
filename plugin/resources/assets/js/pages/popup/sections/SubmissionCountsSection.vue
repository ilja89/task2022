<template>
    <popup-section
        title="Submission counts"
        subtitle="Submission counts and averages for Charons."
    >
        <div v-if="submissionCounts.length" class="card  has-padding">
            <table class="table  is-fullwidth  is-striped  submission-counts__table">
                <thead>
                <tr>
                    <th @click="toggleSorted('project_folder')">
                        Charon
                        <span v-if="sorted[0] === 'project_folder'">
                            {{ sortingArrow }}
                        </span>
                    </th>
                    <th @click="toggleSorted('diff_users', 'asc')">
                        Different users
                        <span v-if="sorted[0] === 'diff_users'">
                            {{ sortingArrow }}
                        </span>
                    </th>
                    <th @click="toggleSorted('tot_subs', 'asc')">
                        Total submissions
                        <span v-if="sorted[0] === 'tot_subs'">
                            {{ sortingArrow }}
                        </span>
                    </th>
                    <th @click="toggleSorted('subs_per_user', 'asc')">
                        Submissions per user
                        <span v-if="sorted[0] === 'subs_per_user'">
                            {{ sortingArrow }}
                        </span>
                    </th>
                    <th @click="toggleSorted('avg_grade', 'asc')">
                        Average grade
                        <span v-if="sorted[0] === 'avg_grade'">
                            {{ sortingArrow }}
                        </span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="charon in sortedCounts">
                    <td>{{ charon.project_folder }}</td>
                    <td>{{ charon.diff_users }}</td>
                    <td>{{ charon.tot_subs }}</td>
                    <td>{{ charon.subs_per_user ? parseFloat(charon.subs_per_user) : 0 }}</td>
                    <td>{{ charon.avg_grade ? parseFloat(charon.avg_grade) : 0 }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <h3 v-if="!submissionCounts.length" class="title  is-3">
            No Charons for this course!
        </h3>
    </popup-section>
</template>

<script>
    import { mapGetters } from 'vuex'
    import { Submission } from '../../../api/index'
    import { PopupSection } from '../layouts/index'

    export default {
        name: 'submission-counts-section',

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
                const [field, direction] = this.sorted

                return this.submissionCounts.sort((a, b) => {
                    let aVal = a[field]
                    let bVal = b[field]
                    const dir = direction === 'desc' ? 1 : -1
                    if (!isNaN(aVal) && !isNaN(bVal)) {
                        aVal = +aVal
                        bVal = +bVal
                    }

                    if (aVal > bVal) {
                        return dir
                    } else if (aVal < bVal) {
                        return -dir
                    } else {
                        return 0
                    }
                })
            },

            sortingArrow() {
                return this.sorted[1] === 'asc'
                    ? '▲'
                    : '▼'
            },
        },

        mounted() {
            this.fetchSubmissionCounts()
            VueEvent.$on('refresh-page', this.fetchSubmissionCounts);
        },

        methods: {
            fetchSubmissionCounts() {
                Submission.findSubmissionCounts(this.courseId, counts => {
                    this.submissionCounts = counts
                })
            },

            toggleSorted(field, defaultDirection = 'desc') {
                if (this.sorted[0] === field) {
                    this.toggleSortingDirection()
                } else {
                    this.sorted = [field, defaultDirection]
                }
            },

            toggleSortingDirection() {
                if (this.sorted[1] === 'asc') {
                    this.sorted = [this.sorted[0], 'desc']
                } else {
                    this.sorted = [this.sorted[0], 'asc']
                }
            },
        },
    }
</script>

<style lang="scss" scoped>

    $columns: 5;

    .submission-counts__table th {
        width: 100% / $columns;
        cursor: pointer;
    }

</style>
