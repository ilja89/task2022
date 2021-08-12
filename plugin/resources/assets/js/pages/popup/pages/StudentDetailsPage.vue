<template>
  <div class="student-overview-container">
    <page-title :title="name"></page-title>

    <popup-section title="Grades report"
                   subtitle="Grading report for the current student.">

      <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <div class="student-overview-card" v-html="table"></div>
        </v-container>
      </v-card>

    </popup-section>

    <popup-section title="Upcoming registrations">
      <defense-registrations-section :teachers="teachers" :defense-list="defenseList"/>
    </popup-section>

    <student-charon-points-vs-course-average-chart
        v-if="student"
        :student="student"
        :average-submissions="averageSubmissions">
    </student-charon-points-vs-course-average-chart>

  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {Submission, User} from '../../../api'
import {PopupSection} from '../layouts'
import {DefenseRegistrationsSection} from '../sections'
import {StudentCharonPointsVsCourseAverageChart} from '../graphics'
import moment from "moment"
import Teacher from "../../../api/Teacher";
import Defense from "../../../api/Defense";

export default {
  components: {PopupSection, PageTitle, DefenseRegistrationsSection, StudentCharonPointsVsCourseAverageChart},

  name: "StudentDetailsPage",

  data() {
    return {
      name: 'Student name',
      table: '',
      averageSubmissions: [],
      after: {time: `${moment().format("YYYY-MM-DD HH:mm")}`},
      before: {time: null},
      filter_teacher: -1,
      filter_progress: null,
      defenseList: [],
      teachers: []
    }
  },

  computed: {
    ...mapState([
      'student',
    ]),

    ...mapGetters([
      'courseId',
    ]),

    routeStudentId() {
      return this.$route.params.student_id
    },
  },

  watch: {
    $route() {
      this.getStudent()
      this.getStudentOverviewTable()
    },
  },

  methods: {
    ...mapActions([
      'fetchStudent',
    ]),

    getStudentOverviewTable() {
      User.getReportTable(this.courseId, this.routeStudentId, (table) => {
        this.table = table
      })
    },

    getStudent() {
      this.fetchStudent({courseId: this.courseId, studentId: this.routeStudentId})
    },

    fetchRegistrations() {
      Defense.filtered(this.courseId, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
        this.defenseList = response.filter(defense => defense.student_id === parseInt(this.routeStudentId))
      })
    },
    setAverageSubmissions(averageSubmissions) {
      this.averageSubmissions = averageSubmissions;
    },
  },

  created() {
    this.getStudent()
    this.getStudentOverviewTable()
    this.fetchRegistrations()
    Teacher.getAllTeachers(this.courseId, response => {
      this.teachers = response
    }),
    Submission.findBestAverageCourseSubmissions(this.courseId, this.setAverageSubmissions)
  },
}
</script>

<style scoped>

</style>
