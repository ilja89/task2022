<template>
  <div class="student-overview-container">
    <page-title :title="page_name"></page-title>
  </div>

</template>

<script>
import {PageTitle} from '../partials'
import {mapGetters, mapState} from 'vuex'
import {Charon, Submission} from "../../../api/index";

export default {
  name: "ActivityDashboardPage",

  components: {PageTitle},

  data() {
    return {
    }
  },

  computed: {
    ...mapState([
      "course",
      "charon"
    ]),

    ...mapGetters([
       "courseId"
    ]),
    version: function () { return window.appVersion; },

    routeCharonId() {
      return this.$route.params.charon_id
    },

    page_name() {
      if (this.charon) {
        return 'Charon dashboard: ' + this.charon.name
      }
      return 'Charon dashboard'
    },
  },

  watch: {
    $route() {
      if (typeof this.routeCharonId !== 'undefined' && this.$route.name === 'activity-dashboard') {
        this.getCharon()
        // this.getSubmissions()
      }
    },
  },

  created() {
    this.getCharon()
    // this.getSubmissions()
  },

  methods: {
    getCharon() {
      Charon.getById(this.routeCharonId, response => {
        this.charon = response
      })
      document.title = this.page_name
    },

    // getSubmissions() {
    //   Submission.findLatest(this.courseId, response => {
    //     this.latestSubmissions = response
    //   })
    // }
  },
}
</script>

<style scoped>

</style>