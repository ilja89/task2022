<template>
  <div class="student-overview-container">

    <page-title :title="charonName"></page-title>

    <general-information-section :charon="charon" :general_information="general_information"></general-information-section>

    <dashboard-latest-submissions-section :latest-submissions="latestSubmissions"></dashboard-latest-submissions-section>

    <dashboard-statistics-section :submission_counts="submission_counts"></dashboard-statistics-section>

    <submission-graph-section :graphDataEveryDay="graphDataEveryDay" :graph-data-today="graphDataToday"></submission-graph-section>

    <charon-defense-registrations-section :defense-list="defenseList" :teachers="teachers"></charon-defense-registrations-section>

    <lab-section :labs="labs" :activity-dashboard-page="true"></lab-section>

    </div>

</template>

<script>
import {mapGetters, mapState} from 'vuex'
import {DashboardStatisticsSection, DashboardLatestSubmissionsSection, GeneralInformationSection, CharonDefenseRegistrationsSection, SubmissionGraphSection} from '../sections'
import {Charon, Submission} from "../../../api/index";
import Lab from "../../../api/Lab";
import LabSection from "../sections/LabSection";
import Statistics from "../../../api/Statistics";
import {PageTitle} from "../partials";

export default {
  name: "ActivityDashboardPage",

  components: {
    LabSection,
    DashboardStatisticsSection, DashboardLatestSubmissionsSection, GeneralInformationSection,
    CharonDefenseRegistrationsSection, SubmissionGraphSection, PageTitle},

  data() {
    return {
      charon: {},
      submission_counts: [],
      latestSubmissions: [],
      defenseList: [],
      teachers: [],
      labs: {},
      labs_countdown: 0,
      graphDataToday: [],
      graphDataEveryDay: [],
      general_information: {},
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

    charonName() {
      if (this.charon) {
        return ' ' + this.charon.name
      }
      return ''
    }
  },

  metaInfo() {
    return {
      title: `${'Charon -' + this.charonName}`
    }
  },

  created() {
    this.getCharon()
    this.fetchSubmissionCounts()
    this.fetchLatestSubmissions()
    this.fetchGraphData()
    this.fetchGeneralInformation()
  },

  methods: {
    getCharon() {
      Charon.getById(this.routeCharonId, response => {
        this.charon = response
      })
    },

    getLabsByCharonId() {
      Lab.getByCharonId(this.routeCharonId, response => {
        this.labs = response
        this.formatLabs(this.labs, (done) => {
          this.assignLabs(done)
        })
      })
    },

    formatLabs(labs, then) {
      this.labs_countdown = labs.length
      for (let i = 0; i < labs.length; i++) {
        let save_start = labs[i].start
        labs[i].start = {time: new Date(save_start)}
        let save_end = labs[i].end
        labs[i].end = {time: new Date(save_end)}
        then(labs)
      }
    },

    assignLabs(futureLabs) {
      this.labs_countdown--
      if (!this.labs_countdown) {
        this.labs = futureLabs;
      }
    },

    fetchSubmissionCounts() {
      Submission.findSubmissionCounts(this.courseId, counts => {
        this.submission_counts = counts.filter(item => item.charon_id === this.routeCharonId).map(item => {
          const container = {};

          container['diff_users'] = item.diff_users;
          container['tot_subs'] = item.tot_subs;
          container['subs_per_user'] = parseFloat(item.subs_per_user).toFixed(1);
          container['avg_defended_grade'] = item.avg_defended_grade;
          container['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toFixed(2);
          container['defended_amount'] = item.defended_amount;

          return container;
        });
      })
    },

    fetchLatestSubmissions() {
      Submission.findLatest(this.courseId, submissions => {
        this.latestSubmissions = submissions.filter(submission => submission.charon.id === this.routeCharonId)
      })
    },

    fetchGraphData() {
      Statistics.getSubmissionDatesCountsForCharon(this.courseId, this.routeCharonId, data => {
        this.graphDataEveryDay = data
      })
      Statistics.getSubmissionCountsForCharonToday(this.courseId, this.routeCharonId, data => {
        this.graphDataToday = data
      })
    },

    fetchGeneralInformation() {
      Statistics.getCharonGeneralInformation(this.courseId, this.routeCharonId, data => {
        this.general_information = data
      })
    },
  },
}
</script>

<style scoped>

</style>