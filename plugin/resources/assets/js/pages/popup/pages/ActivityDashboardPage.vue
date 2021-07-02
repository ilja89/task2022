<template>
  <div class="student-overview-container">
    <page-title :title="page_name"></page-title>

    <dashboard-statistics-section :submission_counts="submission_counts"></dashboard-statistics-section>

    <dashboard-latest-submissions-section :latest-submissions="latestSubmissions"></dashboard-latest-submissions-section>
  </div>

</template>

<script>
import {mapGetters, mapState} from 'vuex'
import {PageTitle} from '../partials'
import {DashboardStatisticsSection, DashboardLatestSubmissionsSection} from '../sections'
import {Charon, Submission} from "../../../api/index";

export default {
  name: "ActivityDashboardPage",

  components: {PageTitle, DashboardStatisticsSection, DashboardLatestSubmissionsSection},

  data() {
    return {
      charon: {},
      submission_counts: [],
      latestSubmissions: []
    }
  },

  computed: {
    ...mapState([
      "course",
    ]),

    ...mapGetters([
       "courseId"
    ]),
    version: function () { return window.appVersion; },

    routeCharonId() {
      return parseInt(this.$route.params.charon_id)
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
        this.fetchSubmissionCounts()
        this.fetchLatestSubmissions()
      }
    },
  },

  created() {
    this.getCharon()
    this.fetchSubmissionCounts()
    this.fetchLatestSubmissions()
  },

  methods: {
    getCharon() {
      Charon.getById(this.routeCharonId, response => {
        this.charon = response
      })
      document.title = this.page_name
    },

    fetchSubmissionCounts() {
      Submission.findSubmissionCounts(this.courseId, counts => {
        this.submission_counts = counts.filter(item => item.charon_id === this.routeCharonId).map(item => {
          const container = {};

          container['diff_users'] = item.diff_users;
          container['tot_subs'] = item.tot_subs;
          container['subs_per_user'] = parseFloat(item.subs_per_user).toPrecision(2);
          container['avg_defended_grade'] = parseFloat(item.avg_defended_grade).toPrecision(2);
          container['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toPrecision(2);

          return container;
        });
      })
    },

    fetchLatestSubmissions() {
      Submission.findLatest(this.courseId, submissions => {
        this.latestSubmissions = submissions.filter(submission => submission.charon.id === this.routeCharonId)
      })
    },
  },
}
</script>

<style scoped>

</style>