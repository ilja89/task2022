<template>
    <popup-section
        title="Plagiarism results"
        subtitle="Here are the results for the latest plagiarism check."
    >

        <template slot="header-right">
            <charon-select/>
        </template>

        <plagiarism-similarities-tabs
            :similarities="similarities"
        />

    </popup-section>
</template>

<script>
    import { mapState } from 'vuex'

    import { PopupSection } from '../layouts'
    import { CharonSelect, PlagiarismSimilaritiesTabs } from '../partials'
    import { Plagiarism } from '../../../api'

    export default {
        name: 'plagiarism-results-section',

        components: { PopupSection, CharonSelect, PlagiarismSimilaritiesTabs },

        data() {
            return {
                similarities: [],
            }
        },

        computed: {
            ...mapState([
                'charon',
            ]),
        },

        methods: {
            fetchSimilarities() {
                if (!this.charon) return

                Plagiarism.fetchSimilarities(this.charon.id, response => {
                    this.similarities = response.similarities
                })
            },
        },
    }
</script>

<style lang="scss" scoped>

    .mb {
        margin-bottom: 2rem;
    }

</style>
