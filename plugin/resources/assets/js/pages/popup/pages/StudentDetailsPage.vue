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
  </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {User} from '../../../api'
import {PopupSection} from '../layouts'

export default {
  components: {PopupSection, PageTitle},

  name: "StudentDetailsPage",

  data() {
    return {
      name: 'Student name',
      table: ''

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

  },

  created() {
    this.getStudent()
    this.getStudentOverviewTable()
  },
}
</script>

<style scoped>

</style>