<template>
  <div>
    <div v-if="submission !== null">
      <submission-component :submission="this.submission" :color="'light-blue darken-0'"/>
    </div>
  </div>
</template>

<script>
import {Submission} from "../../../api";
import SubmissionModal from "./SubmissionModal";
import SubmissionComponent from "./SubmissionComponent";

export default {
  components: {SubmissionModal, SubmissionComponent},

  data() {
    return {
      submission: null
    }
  },

  created() {
    this.getSubmission();
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
    }
  },

};
</script>
