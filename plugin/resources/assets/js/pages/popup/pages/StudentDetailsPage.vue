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

    <student-summary-section :student_summary_data="student_summary"></student-summary-section>

  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {Charon, Defense, Submission, User} from '../../../api'
import {PopupSection} from '../layouts'
import StudentSummarySection from "../sections/StudentSummarySection";


export default {
  components: {PopupSection, PageTitle, StudentSummarySection},

  name: "StudentDetailsPage",

  data() {
    return {
      name: 'Student name',
      table: '',
      student_summary: {
        'total_points_course': 0,
        'total_submissions': 0,
        'defended_charons': 0,
        'upcoming_defences': 0,
        'charons_with_submissions': 0,
        'potential_points': 0
      }

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

    getStudentSummary() {
      Charon.getAllPointsFromCourseForStudent(this.courseId, this.routeStudentId, result => {
        this.student_summary['total_points_course'] = result
      })

      Submission.findAllForUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['total_submissions'] = result
      })

      Submission.findByUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['defended_charons'] = result.filter(sub => sub.finalgrade !== null).length
      })

      Defense.all(this.courseId, result => {
        this.student_summary['upcoming_defences'] = result.filter(defense => defense.student_id == this.student_id).length
      })

      Submission.findCharonsWithSubmissionsForUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['charons_with_submissions'] = result
      })

      User.getPossiblePointsForCourse(this.courseId, this.routeStudentId, result => {
        this.student_summary['potential_points'] = result
      })
    },

  },

  created() {
    this.getStudent()
    this.getStudentOverviewTable()
    this.getStudentSummary()
  },



}
</script>

<style scoped>

</style>