<template>
    <popup-section title="General information"
                   subtitle="Here's some general and critical information about the activity.">

        <v-card class="ges-card" v-if="general_information">
            <v-card-text class="text-card">Average defended points: {{
                    general_information.avgDefenseGrade | gradeFilter
                }}
            </v-card-text>
            <v-card-text class="text-card">Students total: {{ noOfStudents }}</v-card-text>
            <v-card-text class="text-card">Highest score: {{
                    general_information.highestScore | scoreFilter
                }}
            </v-card-text>
            <v-card-text class="text-card">Students defended: {{ general_information.studentsDefended }}</v-card-text>
            <v-card-text class="text-card">Students started: {{ general_information.studentsStarted }}</v-card-text>
            <v-card-text class="text-card">Max points: {{ general_information.maxPoints | gradeFilter }}</v-card-text>
            <v-card-text class="text-card">Students not defended: {{
                    noOfStudents - general_information.studentsDefended
                }}
            </v-card-text>
            <v-card-text class="text-card">Students not started: {{
                    noOfStudents - general_information.studentsStarted
                }}
            </v-card-text>
            <v-card-text class="text-card">Registered for defense: {{ uniqueStudents.length }}</v-card-text>
            <v-card-text class="text-card" v-html="deadlines"></v-card-text>
        </v-card>
        <v-card class="ges-card" v-else>
            <v-card-text class="text-card"> {{ noDataToShow }}</v-card-text>
        </v-card>
    </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";
import {CharonSelect} from '../partials';
import {Defense, Course} from "../../../api/index"
import {mapGetters, mapState} from "vuex";
import moment from "moment";

export default {
    name: "GeneralInformationSection",

    components: {PopupSection, CharonSelect},

    props: ['general_information'],

    data() {
        return {
            noDataToShow: "Can't find data to show",
            noOfStudents: 0,
            uniqueStudents: [],
            defended: []
        }
    },

    filters: {
        gradeFilter: function (value) {
            if (!value) return 'No points yet';
            return parseFloat(value).toFixed(2);
        },

        scoreFilter: function (value) {
            if (!value) return 'No scores yet';
            return parseFloat(value).toFixed(2);
        }
    },

    computed: {
        ...mapState([
            "charon"
        ]),

        ...mapGetters([
            "courseId"
        ]),

        routeCharonId() {
            return parseInt(this.$route.params.charon_id)
        },

        deadlines() {
            if (this.general_information.deadlines && this.general_information.deadlines.length) {
                const deadlines = this.general_information.deadlines
                let deadlinesFormatted = this.formatDeadlines(deadlines)
                return 'Deadlines:\r\n' + deadlinesFormatted.join('\r\n')
            }
            return 'No deadline set for this charon'
        }
    },

    methods: {
        formatDeadlines(deadlines) {
            let formattedDeadlines = []
            let pastDeadlines = []
            let deadlinesAsDates = deadlines.map(deadline => {
                return new Date(deadline.deadline_time)
            })

            let activeDeadline = this.findActiveDeadline(deadlinesAsDates)
            if (!activeDeadline) {
                formattedDeadlines.push(`<b>No active deadline yet</b>`)
            } else {
                pastDeadlines = this.findPastDeadlines(activeDeadline, deadlinesAsDates)
            }

            for (let deadline of deadlines) {
                let deadlineContent = `${deadline.deadline_time} - ${deadline.percentage}%`
                let deadlineAsDate = new Date(deadline.deadline_time).getTime()

                if (activeDeadline && activeDeadline.getTime() === deadlineAsDate) {
                    formattedDeadlines.push(`<b>${deadlineContent}</b>`)
                } else if (pastDeadlines.includes(deadlineAsDate)) {
                    formattedDeadlines.push(`<span style="color: red; text-decoration: line-through">${deadlineContent}</span>`)
                } else {
                    formattedDeadlines.push(`${deadlineContent}`)
                }
            }
            return formattedDeadlines
        },

        findActiveDeadline(dates) {
            let today = new Date()
            let closest = null
            for (let date of dates) {
                if (date < today && (closest === null || date > closest)) {
                    closest = date
                }
            }
            return closest
        },

        findPastDeadlines(activeDeadline, dates) {
            return dates.map(date => {
                if (date < activeDeadline) return date.getTime()
            })
        },

        fetchAllDefenses() {
            Defense.all(this.courseId, data => {
                let charonDefenses = data.filter(item => item.charon_id === this.routeCharonId)
                this.defended = charonDefenses.filter(item => this.isUnique(item.student_id))
            })
        },

        getStudentCount() {
            Course.getCourseStudentCount(this.courseId, data => {
                this.noOfStudents = data
            })
        },

        isUnique(studentId) {
            if (!this.uniqueStudents.includes(studentId)) {
                this.uniqueStudents.push(studentId)
                return true
            } else {
                return false
            }
        },

        getThisCharon() {
            return this.charon
        },

        formatDate(date) {
            return moment(date, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD HH:mm");
        },
    },

    created() {
        this.fetchAllDefenses()
        this.getStudentCount()
    }
}
</script>

<style scoped>
.ges-card {
    display: flex;
    flex-wrap: wrap;
}

.text-card {
    flex: 0 0 33.3333%;
    white-space: pre-line;
}

</style>