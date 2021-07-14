<template>
  <div class="student-overview-container">

    <page-title :title="page_name"></page-title>

    <general-information-section :charon="charon"></general-information-section>

    <dashboard-latest-submissions-section :latest-submissions="latestSubmissions"></dashboard-latest-submissions-section>

    <dashboard-statistics-section :submission_counts="submission_counts"></dashboard-statistics-section>

    <charon-defense-registrations-section :defense-list="defenseList" :teachers="teachers"></charon-defense-registrations-section>

    </div>

</template>

<script>
import {mapGetters, mapState} from 'vuex'
import {PageTitle} from '../partials'
import {DashboardStatisticsSection, DashboardLatestSubmissionsSection, GeneralInformationSection, CharonDefenseRegistrationsSection} from '../sections'
import {Charon, Submission} from "../../../api/index";
import LatestSubmissionsSection from "../sections/LatestSubmissionsSection";

export default {
  name: "ActivityDashboardPage",

  components: {
    LatestSubmissionsSection,
    PageTitle, DashboardStatisticsSection, DashboardLatestSubmissionsSection, GeneralInformationSection,
    CharonDefenseRegistrationsSection},

  data() {
    return {
      charon: {},
      submission_counts: [],
      latestSubmissions: [],
      defenseList: [],
      teachers: []
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
          container['subs_per_user'] = parseFloat(item.subs_per_user).toFixed(1);
          container['avg_defended_grade'] = parseFloat(item.avg_defended_grade).toFixed(1);
          container['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toFixed(1);

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