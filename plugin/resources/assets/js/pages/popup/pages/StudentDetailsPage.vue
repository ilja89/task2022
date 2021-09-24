<template>
  <div class="student-overview-container">
    <page-title :title="studentName"></page-title>

    <student-details-charons-table-section :table="charonsTable"></student-details-charons-table-section>

    <student-summary-section :student_summary_data="student_summary"></student-summary-section>

    <popup-section title="Grades report"
                   subtitle="Grading report for the current student.">

      <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <div class="student-overview-card" v-html="gradesTable"></div>
        </v-container>
      </v-card>

    </popup-section>

    <student-details-submissions-section :latest-submissions="latestSubmissions"></student-details-submissions-section>

    <popup-section title="Upcoming registrations">
      <defense-registrations-section :teachers="teachers" :defense-list="defenseList"/>
    </popup-section>

    <student-charon-points-vs-course-average-chart
        v-if="student"
        :student="student"
        :average-submissions="averageSubmissions">
    </student-charon-points-vs-course-average-chart>

    <comments-section :charonSelector="true"></comments-section>

  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {Charon, Defense, Submission, User} from '../../../api'
import {PopupSection} from '../layouts'
import StudentSummarySection from "../sections/StudentSummarySection";
import {DefenseRegistrationsSection, StudentDetailsSubmissionsSection, StudentDetailsCharonsTableSection, CommentsSection} from '../sections'
import {StudentCharonPointsVsCourseAverageChart} from '../graphics'
import moment from "moment"
import Teacher from "../../../api/Teacher";

export default {
  components: {PopupSection, PageTitle, StudentSummarySection, CommentsSection, DefenseRegistrationsSection, StudentCharonPointsVsCourseAverageChart,StudentDetailsSubmissionsSection, StudentDetailsCharonsTableSection},

  name: "StudentDetailsPage",

  data() {
    return {      
      gradesTable: '',
      charonsTable: [],
      latestSubmissions: [],
      averageSubmissions: [],
      after: {time: `${moment().format("YYYY-MM-DD HH:mm")}`},
      before: {time: null},
      filter_teacher: -1,
      filter_progress: null,
      defenseList: [],
      teachers: [],
      name: 'Student name',
      student: null,
      table: '',
      student_summary: {
        'total_points_course': 0,
        'total_submissions': 0,
        'defended_charons': 0,
        'upcoming_defences': 0,
        'charons_with_submissions': 0,
        'potential_points': 0
      },
    }
  },

  computed: {
    ...mapGetters([
      'courseId',
    ]),

    ...mapState([
      'charons'
    ]),

    routeStudentId() {
      return this.$route.params.student_id
    },

    studentName() {
      return this.student ? this.student.firstname + ' ' + this.student.lastname + ' (' + this.student.username + ')' : "Student"
    }
  },

  methods: {
    getCharonsTable() {
      User.getUserCharonsDetails(this.courseId, this.routeStudentId, data => {
        this.charonsTable = data
      })
    },

    getStudentOverviewTable() {
      User.getReportTable(this.courseId, this.routeStudentId, (table) => {
        this.gradesTable = table
      })
    },

    getStudentSummary() {
      Charon.getAllPointsFromCourseForStudent(this.courseId, this.routeStudentId, result => {
        this.student_summary['total_points_course'] = result
      })

      User.getPossiblePointsForCourse(this.courseId, this.routeStudentId, result => {
        this.student_summary['potential_points'] = result
      })

      Submission.findAllForUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['total_submissions'] = result
      })

      Submission.findCharonsWithSubmissionsForUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['charons_with_submissions'] = result
      })

      Submission.findByUser(this.courseId, this.routeStudentId, result => {
        this.student_summary['defended_charons'] = result.filter(sub => sub.finalgrade > 0).length
      })

      Defense.all(this.courseId, result => {
        this.student_summary['upcoming_defences'] = result.filter(defense => defense.student_id === parseInt(this.routeStudentId)).length
      })
    },

    fetchLatestSubmissions() {
      Submission.findLatestSubmissionsByUser(this.courseId, this.routeStudentId, submissions => {
        this.latestSubmissions = submissions
      })
    },

    fetchRegistrations() {
      Defense.filtered(this.courseId, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
        this.defenseList = response.filter(defense => defense.student_id === parseInt(this.routeStudentId))
      })
    },

    setAverageSubmissions(averageSubmissions) {
      this.averageSubmissions = averageSubmissions;
    }
  },

  created() {
    this.getStudentOverviewTable()
    this.getStudentSummary()
    this.getCharonsTable()
    this.fetchLatestSubmissions()
    this.fetchRegistrations()
    Teacher.getAllTeachers(this.courseId, response => {
      this.teachers = response
    })
    Submission.findBestAverageCourseSubmissions(this.courseId, this.setAverageSubmissions)
    User.getStudentInfo(this.courseId, this.routeStudentId, response => {
      this.student = response
    })
  },

  metaInfo() {
    return {
      title: this.studentName + ' details page'
    }
  }
}
</script>

<style scoped>

</style>