<template>
    <div>

        <page-title title="Plagiarism">
            <button
                class="button is-primary"
                type="button"
                @click="handleRunPlagiarismClicked"
            >
                Run checksuite
            </button>
        </page-title>

        <plagiarism-results-section></plagiarism-results-section>

    </div>
</template>

<script>
    import { mapState } from 'vuex'

    import { PageTitle } from '../partials'
    import { PlagiarismResultsSection } from '../sections'
    import { Plagiarism } from '../../../api'

    export default {
        name: 'plagiarism-page',

        components: { PageTitle, PlagiarismResultsSection },

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
