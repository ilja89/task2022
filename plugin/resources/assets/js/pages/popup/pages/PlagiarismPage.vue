<template>
    <div>

        <page-title title="Plagiarism">
            <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked">Run checksuite</v-btn>
        </page-title>

        <plagiarism-matches-section @matchesFetched="passMatchesToOverview"></plagiarism-matches-section>

        <plagiarism-overview-section :matches="matches"></plagiarism-overview-section>

        <plagiarism-check-history-section></plagiarism-check-history-section>

    </div>
</template>

<script>

import {PageTitle} from '../partials'
import {PlagiarismResultsSection, PlagiarismMatchesSection, PlagiarismOverviewSection} from '../sections'
import PlagiarismCheckHistorySection from "../sections/PlagiarismCheckHistorySection";

export default {
    name: 'plagiarism-page',

    components: {
        PlagiarismCheckHistorySection,
        PlagiarismOverviewSection, PlagiarismMatchesSection, PageTitle, PlagiarismResultsSection},
}
</script>
