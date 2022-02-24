<template>
    <div>

        <page-title title="Plagiarism">
            <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked">Run checksuite</v-btn>
        </page-title>

        <plagiarism-results-section></plagiarism-results-section>

        <plagiarism-matches-section></plagiarism-matches-section>

        <plagiarism-overview-section></plagiarism-overview-section>

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
        },
    }
</script>
