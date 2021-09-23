<template>

  <popup-section title="Student summary"
                 subtitle="Here is the summary from course for selected user">
    <v-data-table
        :headers="summary_headers"
        :items="summary_row"
        :disable-sort="true"
        hide-default-footer
    ></v-data-table>

  </popup-section>
</template>

<script>
import {PopupSection} from '../layouts/index'
import {Charon, Course, Defense, Submission, User} from "../../../api";

export default {
  name: "student-summary-section",

  data() {
    return {
      summary_headers: [
        {text: 'Total points for course', value: 'total_points_course', align: 'center'},
        {text: 'Potential/unclaimed points for course', value: 'potential_points', align: 'center'},
        {text: 'Total number of submissions', value: 'total_submissions', align: 'center'},
        {text: 'Charons with submissions', value: 'charons_with_submissions', align: 'center'},
        {text: 'Defended charons', value: 'defended_charons', align: 'center'},
        {text: 'Upcoming defences', value: 'upcoming_defences', align: 'center'}
      ],
    }
  },

  props: {
    student_summary_data: {
      required: true,
      default: {}
    },
  },

  computed: {
    summary_row() {
      return [{
        total_points_course: this.student_summary_data.total_points_course,
        potential_points: this.student_summary_data.potential_points,
        total_submissions: this.student_summary_data.total_submissions, //
        charons_with_submissions: this.student_summary_data.charons_with_submissions,
        defended_charons: this.student_summary_data.defended_charons, //
        upcoming_defences: this.student_summary_data.upcoming_defences, //
      }];
    }

  },

  components: {PopupSection},
}
</script>