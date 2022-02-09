<template>
  <popup-section
      title="Plagiarism matches"
  >

    <template slot="header-right">
      <charon-select/>
      <v-btn @click="fetchMatches()">Fetch Matches</v-btn>
    </template>

    <v-data-table
      :headers="headers"
      :items="matches">
      <template v-slot:item.actions="{ item }">
        <v-row>
          <v-btn icon @click="">
            <v-icon aria-label="Submission Information" role="button" aria-hidden="false">mdi-eye</v-icon>
          </v-btn>
        </v-row>
      </template>
    </v-data-table>
    <pre v-html="matches" style="max-height: 900px"></pre>

  </popup-section>
</template>

<script>
import { mapState } from 'vuex'

import { PopupSection } from '../layouts'
import { CharonSelect, PlagiarismSimilaritiesTabs } from '../partials'
import { Plagiarism } from '../../../api'

export default {
  name: 'plagiarism-matches-section',

  components: { PopupSection, CharonSelect, PlagiarismSimilaritiesTabs },

  data() {
    return {
      matches: [],
      headers: [
        {text: 'Matches', align: 'start', value: 'lines_matched'},
        {text: 'Uni-ID', value: 'submission.gitlab_project.owner.uniid'},
        {text: 'Percentage', value: 'percentage'},
        {text: 'Other Uni-ID', value: 'other_submission.gitlab_project.owner.uniid'},
        {text: 'Other Percentage', value: 'other_percentage'},
        {text: 'Actions', value: 'actions'},
      ]
    }
  },

  computed: {
    ...mapState([
      'charon',
    ]),
  },

  methods: {
    fetchMatches() {
      if (!this.charon) return

      Plagiarism.fetchMatches(this.charon.id, response => {
        this.matches = response
      })
    },
  },
}
</script>
