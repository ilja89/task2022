<template>
    <div>

        <page-title title="Plagiarism">
        </page-title>

        <plagiarism-matches-section
            @matchesFetched="passMatchesToOverview"
            @filtrationOn="warnAboutFiltration"
        ></plagiarism-matches-section>

        <plagiarism-overview-section
            :filtrationOn="filtrationOn"
            :matches="matches"
        ></plagiarism-overview-section>

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
        PlagiarismOverviewSection,
        PlagiarismMatchesSection,
        PageTitle,
        PlagiarismResultsSection
    },

    data() {
        return {
            matches: null,
            filtrationOn: false,
        }
    },

    methods: {
        passMatchesToOverview(matchesFromMoss) {
            this.matches = matchesFromMoss
        },

        warnAboutFiltration(bool) {
            this.filtrationOn = bool
        },
    }
}
</script>
