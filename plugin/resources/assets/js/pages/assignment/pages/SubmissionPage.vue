<template>
  <div>
    <div v-if="this.submission !== null">
      <submission-component :submission="this.submission" :color="getColor(this.submission)"/>
    </div>
  </div>
</template>

<script>
import {Submission} from "../../../api";
import SubmissionComponent from "../components/SubmissionComponent";
import {getSubmissionWeightedScore} from "../helpers/submission";
import {mapState} from "vuex";

export default {
  components: {SubmissionComponent},

  data() {
    return {
      submission: null
    }
  },

  created() {
    this.getSubmission();
  },

  computed: {
    ...mapState([
      'registrations'
    ]),
  },

  methods: {

    getSubmission() {
      Submission.findById(this.$route.params.submission_id, null, submission => {
          this.submission = submission;
          }
      );
    },

    guardFromNavigation(state) {
      this.guard_navigation = state;
    },

    getColor(submission) {
      if (this.defendedSubmission(submission)) return 'success'
      else if (Number.parseFloat(getSubmissionWeightedScore(submission)) < 0.01) return 'red';
      else if (this.registeredSubmission(submission.id)) return 'teal';
      else return `light-blue darken-${this.getColorDarknessByPercentage(getSubmissionWeightedScore(submission) / 100)}`;
    },

    defendedSubmission(submission) {
      try {
        const last = submission.results[submission.results.length - 1];
        return parseFloat(last['calculated_result']) !== 0.0 && last['grade_type_code'] === 1001;
      } catch (e) {
        return false
      }
    },

    registeredSubmission(submissionId) {
      let test = this.registrations.find(x => x.submission_id === submissionId);
      if (test != null) {
        test = test['submission_id'];
        return submissionId === test;
      }
    },

    getColorDarknessByPercentage(percentage, maxDarkness = 3) {
      return maxDarkness - Math.floor(maxDarkness * percentage);
    },
  },

};
</script>
