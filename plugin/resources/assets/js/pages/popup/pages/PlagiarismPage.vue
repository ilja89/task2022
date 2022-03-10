<template>
    <div>

        <page-title title="Plagiarism">
            <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked">Run checksuite</v-btn>
        </page-title>

        <plagiarism-results-section></plagiarism-results-section>

        <plagiarism-matches-section @matchesFetched="passMatchesToOverview"></plagiarism-matches-section>

        <plagiarism-overview-section :matches="matches"></plagiarism-overview-section>

    </div>
</template>

<script>
import {mapState} from 'vuex'

import {PageTitle} from '../partials'
import {PlagiarismResultsSection, PlagiarismMatchesSection, PlagiarismOverviewSection} from '../sections'
import {Plagiarism} from '../../../api'

export default {
    name: 'plagiarism-page',

    components: {PlagiarismOverviewSection, PlagiarismMatchesSection, PageTitle, PlagiarismResultsSection},

    data() {
        return {
            matches: null
        }
    },

    computed: {
        ...mapState([
            'charon',
        ]),
    },

    methods: {
        handleRunPlagiarismClicked() {
            Plagiarism.runPlagiarism(this.charon.id, response => {
                window.VueEvent.$emit(
                    'show-notification',
                    response.message,
                    'success',
                )
            })
        },

        passMatchesToOverview(matchesFromMoss) {
            this.matches = matchesFromMoss
        },
    },
}
</script>
