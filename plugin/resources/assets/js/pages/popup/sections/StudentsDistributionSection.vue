<template>
    <popup-section title="Students distribution"
                   subtitle="Distribution of students over their max grade for this course.">

        <v-data-table
                hide-default-footer
                :headers="student_distribution_headers"
                :items="distributions">
        </v-data-table>

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
                student_distribution: [],
                student_distribution_headers: [
                    {text: 'Points', value: 'interval', align: 'start'},
                    {text: 'Number of students', value: 'user_count'},
                ]
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            distributions() {
                if (!this.student_distribution.length) {
                    return this.student_distribution
                }

                const maxGrade = this.student_distribution[0].max_grade
                const nrOfParts = this.student_distribution.length
                const partSize = maxGrade / nrOfParts

                return this.student_distribution
                    .sort((a, b) => {
                        return a.part > b.part
                    })
                    .map(distribution => {
                        const minGrade = this.round(distribution.part * partSize)
                        const maxGrade = this.round(distribution.part * partSize + partSize)
                        const container = {...distribution, minGrade, maxGrade}
                        container['interval'] = `${minGrade} - ${maxGrade}`
                        return container;
                    })
            },
        },

        methods: {
            fetchStudentsDistribution() {
                User.getStudentsDistribution(this.courseId, student_distribution => {
                    this.student_distribution = student_distribution
                })
            },

            round(nr, precision = 2) {
                const helper = Math.pow(10, precision)
                return Math.round(nr * helper) / helper
            },
        },

        created() {
            this.fetchStudentsDistribution()
        },
    }
</script>
