<template>
    <div>

        <v-card class="mb-16 pl-4">
          <v-card-title>
            Plagiarism
            <div style="float: right">
              <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked">Run plagiarism check</v-btn>
              <charon-select/>
            </div>
          </v-card-title>
        </v-card>

        <plagiarism-check-history-section></plagiarism-check-history-section>

        <plagiarism-matches-section></plagiarism-matches-section>

        <plagiarism-overview-section></plagiarism-overview-section>

    </div>
</template>

<script>
import {mapState} from 'vuex'

import {PageTitle, CharonSelect} from '../partials'
import {PlagiarismResultsSection, PlagiarismMatchesSection, PlagiarismOverviewSection} from '../sections'
import PlagiarismCheckHistorySection from "../sections/PlagiarismCheckHistorySection";

export default {
    name: 'plagiarism-page',

    components: {
        PlagiarismCheckHistorySection,
        PlagiarismOverviewSection, PlagiarismMatchesSection, PageTitle, PlagiarismResultsSection, CharonSelect},

    computed: {
        ...mapState([
            'charon',
            'course'
        ]),
    },

    data() {
        return {
            latestCheck: [],
            interval: null
        }
    },


    methods: {
        handleRunPlagiarismClicked() {
            VueEvent.$emit('run-plagiarism-check');
        }
    },
    beforeDestroy () {
        clearInterval(this.interval)
    },
}
</script>
