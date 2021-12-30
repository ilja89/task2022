<template>
  <div>
    <div v-if="this.submission !== null">
      <submission-component :submission="this.submission" :color="getColor(this.submission, registrations)"/>
    </div>
  </div>
</template>

<script>
import {Submission} from "../../../api";
import SubmissionComponent from "../components/SubmissionComponent";
import {getColor} from "../helpers/modalformatting";
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
    getColor,

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
