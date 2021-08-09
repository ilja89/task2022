<template>
  <div class="student-overview-container">
    <page-title :title="name"></page-title>

    <student-details-submissions-section :latest-submissions="latestSubmissions"></student-details-submissions-section>

    <popup-section title="Grades report"
                   subtitle="Grading report for the current student.">

      <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <div class="student-overview-card" v-html="table"></div>
        </v-container>
      </v-card>

    </popup-section>
  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {Submission, User} from '../../../api'
import {PopupSection} from '../layouts'
import StudentDetailsSubmissionsSection from "../sections/StudentDetailsSubmissionsSection";

export default {
  components: {StudentDetailsSubmissionsSection, PopupSection, PageTitle},

  name: "StudentDetailsPage",

  data() {
    return {
      name: 'Student name',
      table: '',
      latestSubmissions: []
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
      this.fetchLatestSubmissions()
    }
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

    fetchLatestSubmissions() {
      Submission.findLatestSubmissionsByUser(this.courseId, this.routeStudentId, submissions => {
        this.latestSubmissions = submissions
      })
      }

  },

  created() {
    this.getStudent()
    this.getStudentOverviewTable()
    this.fetchLatestSubmissions()
  }
}
</script>

<style scoped>

</style>