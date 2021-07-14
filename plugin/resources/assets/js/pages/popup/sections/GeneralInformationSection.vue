<template>
  <popup-section title="General information"
                 subtitle="Here's some general information about the activity.">
    <div class="name">Charon name: {{ charon.name }}</div>
    <v-card class="ges-card">
      <v-card-text class="text-card">Max points: {{  maxPoints }}</v-card-text>
      <v-card-text class="text-card">Deadline: {{ charon.defense_deadline }}</v-card-text>
      <v-card-text class="text-card">Students total: {{ noOfStudents }} (hardcoded)</v-card-text>
      <v-card-text class="text-card">Students started: {{ submission_count['diff_users'] }}</v-card-text>
      <v-card-text class="text-card">Students not started: {{ noOfStudents - submission_counts[0]['diff_users'] }}</v-card-text>
      <v-card-text class="text-card">Students defended: {{ submission_counts[0]['defended_amount'] }}</v-card-text>
      <v-card-text class="text-card">Students not defended: {{ submission_counts[0]['diff_users'] - submission_counts[0]['defended_amount'] }}</v-card-text>
      <v-card-text class="text-card">Registered for defense: {{  uniqueStudents.length }}</v-card-text>
      <v-card-text class="text-card">Average defended points: {{ parseFloat(submission_counts[0]['avg_defended_grade']).toFixed(1) }}</v-card-text>
    </v-card>
  </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";
import {Submission, Defense} from "../../../api/index"
import {mapGetters, mapState} from "vuex";

export default {
  name: "GeneralInformationSection",

  components: {PopupSection},

  props: ['charon'],

  data() {
    return {
      submission_counts: [],
      defended: [],
      noOfStudents: 469,
      uniqueStudents: [],
      submission_count: {},
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

    maxPoints() {
      let thisCharon = {};
      if (this.$store.state.charons) {
        const charons = this.$store.state.charons;
        for (let charonIndex in charons) {
          if (charons[charonIndex].id === parseInt(this.$route.params.charon_id)) {
            thisCharon = charons[charonIndex];
          }
        }
        if (thisCharon['grademaps']) {
          let maxPoints = thisCharon['grademaps']['0']['grade_item']['grademax'];
          return parseFloat(maxPoints).toFixed(1);
        }
      }
      return ''
    }
  },

  watch: {
    $route() {
      if (typeof this.routeCharonId !== 'undefined' && this.$route.name === 'activity-dashboard') {
        this.fetchSubmissionCounts()
      }
    },
  },

  methods: {
    fetchSubmissionCounts() {
      Submission.findSubmissionCounts(this.courseId, counts => {
        this.submission_counts = counts.filter(item => item.charon_id === this.routeCharonId)
        // console.log(this.submission_counts)
        if (this.submission_counts) {
          this.submission_count['diff_users'] = this.submission_counts['0']['diff_users'];
        }

        // submission_counts['diff_users'] = item.diff_users;
        // submission_counts['tot_subs'] = item.tot_subs;
        // submission_counts['subs_per_user'] = parseFloat(item.subs_per_user).toPrecision(2);
        // submission_counts['avg_defended_grade'] = parseFloat(item.avg_defended_grade).toPrecision(2);
        // submission_counts['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toPrecision(2);

      })
    },

    fetchDefenseAll() {
      Defense.all(this.courseId, data => {
        let charonDefenses = data.filter(item => item.charon_id === this.routeCharonId)
        this.defended = charonDefenses.filter(item => this.isUnique(item.student_id))
      })
    },

    isUnique(studentId) {
      if (!this.uniqueStudents.includes(studentId)) {
        this.uniqueStudents.push(studentId)
        return true
      } else {
        return false
      }
    }
  },

  created() {
    this.fetchSubmissionCounts()
    this.fetchDefenseAll()
  }
}
</script>

<style scoped>
.ges-card {
  display: flex;
  flex-wrap: wrap;
}

.text-card {
  flex: 0 0 33.3333%;
}

.name {
  padding: 0 16px 16px;
}

</style>