<template>
  <popup-section title="Submission statistics">

    <v-card-title v-if="submissionsExist">
      Results
      <v-spacer></v-spacer>
    </v-card-title>
    <v-card-title v-else>
      {{ empty }}
    </v-card-title>

    <v-data-table
        v-if="submissionsExist"
        :headers="submission_count_headers"
        :items="submission_counts"
        hide-default-footer>
    </v-data-table>

  </popup-section>
</template>

<script>
import {PopupSection} from '../layouts/index'

export default {
  name: 'dashboard-statistics-section',

  components: {PopupSection},

  data() {
    return {
      empty: 'No submissions for this charon!',
      submission_count_headers: [
        {text: 'Different users', value: 'diff_users'},
        {text: 'Total submissions', value: 'tot_subs'},
        {text: 'Submissions per user', value: 'subs_per_user'},
        {text: 'Average test grade', value: 'avg_raw_grade'},
      ],
    }
  },

  props: {
    submission_counts: {
      required: true,
      default: [],
      type: Array
    },
  },

  computed: {
    submissionsExist() {
      return !!(this.submission_counts.length && this.submission_counts[0].tot_subs !== 0);
    }
  },
}
</script>
