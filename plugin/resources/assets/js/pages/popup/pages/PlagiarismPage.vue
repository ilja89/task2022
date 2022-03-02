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
            <v-card-text>
                Latest check => {{this.latestCheck.charonName}} {{this.latestCheck.created_at}}  {{this.latestCheck.updated_at}} {{this.latestCheck.status}}
            </v-card-text>
        </v-card>

        <plagiarism-results-section></plagiarism-results-section>

        <plagiarism-matches-section></plagiarism-matches-section>

        <plagiarism-overview-section></plagiarism-overview-section>

    </div>
</template>

<script>
import {mapState} from 'vuex'

import {PageTitle, CharonSelect} from '../partials'
import {PlagiarismResultsSection, PlagiarismMatchesSection, PlagiarismOverviewSection} from '../sections'
import {Plagiarism} from '../../../api'

export default {
    name: 'plagiarism-page',

    components: {PlagiarismOverviewSection, PlagiarismMatchesSection, PageTitle, PlagiarismResultsSection, CharonSelect},

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
            Plagiarism.runPlagiarismCheck(this.course.id, this.charon.id, response => {
                this.latestCheck = response.status
                window.VueEvent.$emit(
                    'show-notification',
                    response.message,
                    'success',
                )
            })
            this.refreshLatestStatus();
        },
        refreshLatestStatus() {
            this.interval = setInterval(this.getLatestStatus, 5000)
        },
        getLatestStatus() {
            Plagiarism.getLatestCheckStatus(this.charon.id, this.latestCheck.checkId, response => {
                this.latestCheck = response
                if (this.latestCheck.status === "Check successful") {
                    clearInterval(this.interval)
                }
            })
        }
    },
    beforeDestroy () {
        clearInterval(this.interval)
    },
}
</script>
