<template>
  <popup-section
      title="Plagiarism matches"
  >

    <template slot="header-right">
      <charon-select/>
      <v-btn @click="fetchMatches()">Fetch Matches</v-btn>
    </template>
    <div>
      <v-data-table
          :headers="headers"
          :items="matches">
        <template v-slot:item.actions="{ item }">
          <v-row>
            <plagiarism-match-modal :match="item"></plagiarism-match-modal>
          </v-row>
        </template>
      </v-data-table>
      <pre v-html="matches" style="max-height: 900px; width: 1000px"></pre>
    </div>

  </popup-section>
</template>

<script>
import { mapState } from 'vuex'

import { PopupSection } from '../layouts'
import { CharonSelect, PlagiarismSimilaritiesTabs } from '../partials'
import { Plagiarism } from '../../../api'
import PlagiarismMatchModal from "../partials/PlagiarismMatchModal";

export default {
  name: 'plagiarism-matches-section',

  components: {PlagiarismMatchModal, PopupSection, CharonSelect, PlagiarismSimilaritiesTabs },

  data() {
    return {
      matches: [],
      headers: [
        {text: 'Matches', align: 'start', value: 'lines_matched'},
        {text: 'Uni-ID', value: 'submission.gitlab_project.owner.uniid'},
        {text: 'Percentage', value: 'percentage'},
        {text: 'Other Uni-ID', value: 'other_submission.gitlab_project.owner.uniid'},
        {text: 'Other Percentage', value: 'other_percentage'},
        {text: 'Status', value: 'status'},
        {text: 'Actions', value: 'actions', sortable: false},
      ]
    }
  },

  computed: {
    ...mapState([
      'charon',
      'course'
    ]),
  },

  methods: {
    fetchMatches() {
      if (!this.charon) return

      Plagiarism.fetchMatches(this.course.id, this.charon.id, response => {
        this.matches = response
      })
    },

  },
}
</script>