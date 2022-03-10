<template>
    <div>

        <v-card class="mb-16 pl-4">
          <v-card-title>
            Plagiarism
            <div style="float: right">
              <charon-select/>
            </div>
          </v-card-title>
        </v-card>

        <plagiarism-matches-section></plagiarism-matches-section>

        <plagiarism-overview-section></plagiarism-overview-section>

        <plagiarism-check-history-section></plagiarism-check-history-section>

    </div>
</template>

<script>

import {PageTitle, CharonSelect} from '../partials'
import {PlagiarismResultsSection, PlagiarismMatchesSection, PlagiarismOverviewSection} from '../sections'
import PlagiarismCheckHistorySection from "../sections/PlagiarismCheckHistorySection";

export default {
    name: 'plagiarism-page',

    components: {
        PlagiarismCheckHistorySection,
        PlagiarismOverviewSection, PlagiarismMatchesSection, PageTitle, PlagiarismResultsSection, CharonSelect},
}
</script>
