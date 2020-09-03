<template>
    <popup-section
            title="Students distribution"
            subtitle="Distribution of students over their max grade for this course."
    >
        <v-card
                class="mx-auto"
                outlined
                raised
                shaped
        >

            <table class="table  is-fullwidth  is-striped">
                <thead>
                <tr>
                    <th>Points</th>
                    <th>Number of students</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="distribution in distributions">
                    <td>{{ distribution | distributionInterval }}</td>
                    <td>{{ distribution.user_count }}</td>
                </tr>
                </tbody>
            </table>

        </v-card>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {PopupSection} from '../layouts'
    import {User} from '../../../api'

    export default {
        name: "students-by-total-points-section",

        components: {PopupSection},

        data() {
            return {
                studentsDistribution: [],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            distributions() {
                if (!this.studentsDistribution.length) {
                    return this.studentsDistribution
                }

                const maxGrade = this.studentsDistribution[0].max_grade
                const nrOfParts = this.studentsDistribution.length
                const partSize = maxGrade / nrOfParts

                return this.studentsDistribution
                    .sort((a, b) => {
                        return a.part > b.part
                    })
                    .map(distribution => {
                        const minGrade = this.round(distribution.part * partSize)
                        const maxGrade = this.round(distribution.part * partSize + partSize)
                        return {...distribution, minGrade, maxGrade}
                    })
            },
        },

        filters: {
            distributionInterval(distribution) {
                return `${distribution.minGrade} - ${distribution.maxGrade}`
            },
        },

        methods: {
            fetchStudentsDistribution() {
                User.getStudentsDistribution(this.courseId, studentsDistribution => {
                    this.studentsDistribution = studentsDistribution
                })
            },

            round(nr, precision = 2) {
                const helper = Math.pow(10, precision)
                return Math.round(nr * helper) / helper
            },
        },

        mounted() {
            this.fetchStudentsDistribution()
        },
    }
</script>
