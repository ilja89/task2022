<template>
  <popup-section title="General information"
                 subtitle="Here's some general and critical information about the activity.">

    <template slot="header-right">
      <v-spacer></v-spacer>
      <charon-select/>
    </template>

    <v-card class="ges-card" v-if="general_information">
      <v-card-text class="text-card">Max points: {{  maxPoints }}</v-card-text>
      <v-card-text class="text-card">Deadline: {{ firstDeadline }}</v-card-text>
      <v-card-text class="text-card">Students total: {{ noOfStudents }}</v-card-text>
      <v-card-text class="text-card">Students started: {{ general_information.studentsStarted }}</v-card-text>
      <v-card-text class="text-card">Students not started: {{ noOfStudents - general_information.studentsStarted }}</v-card-text>
      <v-card-text class="text-card">Students defended: {{ general_information.studentsDefended }}</v-card-text>
      <v-card-text class="text-card">Students not defended: {{ general_information.studentsStarted - general_information.studentsDefended }}</v-card-text>
      <v-card-text class="text-card">Registered for defense: {{  uniqueStudents.length }}</v-card-text>
      <v-card-text class="text-card">Average defended points: {{ general_information.avgDefenseGrade | avgDefGradeFilter }}</v-card-text>
    </v-card>
    <v-card class="ges-card" v-else>
      <v-card-text class="text-card"> {{  noDataToShow }} </v-card-text>
    </v-card>
  </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";
import { CharonSelect } from '../partials';
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
    avgDefGradeFilter: function(value) {
      if (!value) return 'No points yet';
      return parseFloat(value).toFixed(2);
    }
  },

  computed: {
    ...mapState([
      "charon",
    ]),

    ...mapGetters([
      "courseId"
    ]),
    version: function () { return window.appVersion; },

    routeCharonId() {
      return parseInt(this.$route.params.charon_id)
    },

    maxPoints() {
        if (this.charon && this.charon['grademaps']) {
            let maxPoints = this.charon['grademaps']['0']['grade_item']['grademax'];
            return parseFloat(maxPoints).toFixed(2);
        }

        return 'Unable to display points'
    },

    firstDeadline() {
        if (this.charon) {
            let deadline = null;
            if (this.charon.deadlines) {
                this.charon.deadlines.forEach(newDeadline => {
                if (deadline === null || newDeadline.deadline_time < deadline) {
                    deadline = newDeadline.deadline_time;
                    }
                })
            } else if (this.charon.defense_deadline) {
                deadline = this.charon.defense_deadline
            }
            return deadline ? this.formatDate(deadline) : 'No deadline for this charon';
        }
        return 'Unable to display deadline';
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