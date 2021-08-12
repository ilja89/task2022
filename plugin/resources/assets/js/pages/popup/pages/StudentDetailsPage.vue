<template>
  <div class="student-overview-container">
    <page-title :title="name"></page-title>

    <student-details-charons-table-section :table="charonsTable"></student-details-charons-table-section>

    <popup-section title="Grades report"
                   subtitle="Grading report for the current student.">

      <v-card class="mx-auto" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>
          <div class="student-overview-card" v-html="gradesTable"></div>
        </v-container>
      </v-card>

    </popup-section>
  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {User} from '../../../api'
import {PopupSection} from '../layouts'
import {StudentDetailsCharonsTableSection} from "../sections";

export default {
  components: {PopupSection, PageTitle, StudentDetailsCharonsTableSection},

  name: "StudentDetailsPage",

  data() {
    return {
      name: 'Student name',
      gradesTable: '',
      charonsTable: []
    }
  },

  computed: {
    ...mapState([
      'student',
    ]),

    ...mapGetters([
      'courseId',
    ]),

    ...mapState([
      'charons'
    ]),

    routeStudentId() {
      return this.$route.params.student_id
    }
  },

  watch: {
    $route() {
      if (typeof this.routeStudentId !== 'undefined' && this.$route.name === 'student-details') {
        this.getStudent()
        this.getStudentOverviewTable()
        this.getCharonsTable()
      }
    }
  },

  methods: {
    ...mapActions([
      'fetchStudent',
    ]),

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

    getStudent() {
      this.fetchStudent({courseId: this.courseId, studentId: this.routeStudentId})
    }
  },

  created() {
    this.getStudent()
    this.getStudentOverviewTable()
    this.getCharonsTable()
  },
}
</script>

<style scoped>

</style>