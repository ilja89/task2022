<template>
    <popup-section title="General information"
                   subtitle="Here's some general and critical information about the activity.">

        <v-card class="ges-card" v-if="general_information">
            <v-card-text class="text-card">Average defended points: {{
                    general_information.avgDefenseGrade | gradeFilter
                }}
            </v-card-text>
            <v-card-text class="text-card">Students total: {{ noOfStudents }}</v-card-text>
            <v-card-text class="text-card">Deadline: {{ deadlines }}</v-card-text>
            <v-card-text class="text-card">Students defended: {{ general_information.studentsDefended }}</v-card-text>
            <v-card-text class="text-card">Students started: {{ general_information.studentsStarted }}</v-card-text>
            <v-card-text class="text-card">Max points: {{ general_information.maxPoints | gradeFilter }}</v-card-text>
            <v-card-text class="text-card">Students not defended: {{
                    general_information.studentsStarted -
                    general_information.studentsDefended
                }}
            </v-card-text>
            <v-card-text class="text-card">Students not started: {{
                    noOfStudents - general_information.studentsStarted
                }}
            </v-card-text>
            <v-card-text class="text-card">Registered for defense: {{ uniqueStudents.length }}</v-card-text>
            <v-card-text class="text-card">Highest score: {{
                    general_information.highestScore | scoreFilter
                }}
            </v-card-text>
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
            if (!value) return 'No score yet';
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
        version: function () {
            return window.appVersion;
        },

        routeCharonId() {
            return parseInt(this.$route.params.charon_id)
        },

        deadlines() {
            if (this.general_information.deadlines) {
                const deadlines = this.general_information.deadlines
                let deadlinesFormatted = [];
                for (let i = 0; i < this.general_information.deadlines.length; i++) {
                    let deadlineWithPercentage = `${deadlines[i].deadline_time} - ${deadlines[i].percentage}%`
                    deadlinesFormatted.push(deadlineWithPercentage)
                }
                return deadlinesFormatted.join(' || ');
            }
            return 'No deadline set for this charon';
        }
    },

    methods: {
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
}

</style>