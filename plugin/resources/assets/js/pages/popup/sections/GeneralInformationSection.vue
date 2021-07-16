<template>
  <popup-section title="General information"
                 subtitle="Here's some general and critical information about the activity.">
    <div class="name">Charon name: {{ charon.name }}</div>
    <v-card class="ges-card" v-if="submission_counts">
      <v-card-text class="text-card">Max points: {{  maxPoints }}</v-card-text>
      <v-card-text class="text-card">Deadline: {{ charon.defense_deadline }}</v-card-text>
      <v-card-text class="text-card">Students total: {{ noOfStudents }}</v-card-text>
      <v-card-text class="text-card">Students started: {{ submission_counts['diff_users'] }}</v-card-text>
      <v-card-text class="text-card">Students not started: {{ noOfStudents - submission_counts['diff_users'] }}</v-card-text>
      <v-card-text class="text-card">Students defended: {{ submission_counts['defended_amount'] }}</v-card-text>
      <v-card-text class="text-card">Students not defended: {{ submission_counts['diff_users'] - submission_counts['defended_amount'] }}</v-card-text>
      <v-card-text class="text-card">Registered for defense: {{  uniqueStudents.length }}</v-card-text>
      <v-card-text class="text-card">Average defended points: {{ parseFloat(submission_counts['avg_defended_grade']).toFixed(1) }}</v-card-text>
    </v-card>
    <v-card class="ges-card" v-else>
      <v-card-text class="text-card"> {{  noDataToShow }} </v-card-text>
    </v-card>
  </popup-section>
</template>

<script>
import {PopupSection} from "../layouts";
import {Defense, Course} from "../../../api/index"
import {mapGetters, mapState} from "vuex";

export default {
  name: "GeneralInformationSection",

  components: {PopupSection},

  props: ['charon', 'submission_counts'],

  data() {
    return {
      noDataToShow: "Can't find data to show",
      noOfStudents: 0,
      uniqueStudents: [],
      defended: []
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
      }
    },
  },

  methods: {
    fetchAllDefenses() {
      Defense.all(this.courseId, data => {
        let charonDefenses = data.filter(item => item.charon_id === this.routeCharonId)
        this.defended = charonDefenses.filter(item => this.isUnique(item.student_id))
      })
    },

    getStudentCount() {
      Course.getCourseStudentCount(this.courseId, data => {
        this.noOfStudents = data
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
    this.fetchAllDefenses()
    this.getStudentCount()
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