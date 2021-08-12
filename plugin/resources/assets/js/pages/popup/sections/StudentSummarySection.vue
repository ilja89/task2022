<template>

  <popup-section title="Student summary"
                 subtitle="Here is the summary from course for current user">
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
import {Charon, Course, Submission, User} from "../../../api";

export default {
  name: "student-summary-section",

  props: {
    student_id: {},
    course_id: '',
  },

  data() {
    return {
      test1: 0,
      test2: [],
      summary_headers: [
        {text: 'Total points from course', value: 'total_points_course', align: 'center'},
        {text: 'Potential points', value: 'potential_points', align: 'center'},
        {text: 'Total number of submissions', value: 'total_submissions', align: 'center'},
        {text: 'Charons with submissions', value: 'charons_with_submissions', align: 'center'},
        {text: 'Defended charons', value: 'defended_charons', align: 'center'},
        {text: 'Upcoming defences', value: 'upcoming_defences', align: 'center'}
      ],
    }
  },

  computed: {

    summary_row() {
      return [{
        total_points_course: 5,
        potential_points: 8,
        total_submissions: 6,
        charons_with_submissions: 4,
        defended_charons: 2,
        upcoming_defences: 1,
      }];
    }

  },

  components: {PopupSection},


  //working methods are currently disabled by //
  created() {
    // this.getTotalPoints()
    // this.getTotalSubmissions()
  },

  methods: {

    getTotalPoints() {
      Charon.getAllPointFromCourseForStudent(this.course_id, this.student_id, result => {
        this.test1 = result
      })
    },

    getTotalSubmissions() {
      Submission.findAllForUser(this.course_id, this.student_id, result => {
        this.test1 = result
      })
    },

  },
}
</script>