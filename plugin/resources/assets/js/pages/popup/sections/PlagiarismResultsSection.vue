<template>
    <popup-section
        title="Plagiarism results"
        subtitle="Here are the results for the latest plagiarism check."
    >

        <template slot="header-right">
            <charon-select/>
        </template>

        <div v-for="similarity in similarities">
            <h3
                class="title  is-3  mb"
                v-if="similarity.state === 'PLAGIARISM_SERVICE_FAILED'"
            >
                Plagiarism service
                <strong class="has-text-weight-semibold">
                    {{similarity.name}}
                </strong>
                has failed.
            </h3>
            <h3
                class="title  is-3  mb"
                v-if="similarity.state === 'PLAGIARISM_SERVICE_PROCESSING'"
            >
                Plagiarism service
                <strong class="has-text-weight-semibold">
                    {{similarity.name}}
                </strong>
                is processing.
            </h3>
            <div
                v-if="similarity.state === 'PLAGIARISM_SERVICE_SUCCESS'"
                class="mb"
            >
                <plagiarism-similarities-table
                    :similarity="similarity"
                />
            </div>
        </div>

    </popup-section>
</template>

<script>
    import { mapState } from 'vuex'

    import { PopupSection } from '../layouts'
    import { CharonSelect, PlagiarismSimilaritiesTable } from '../partials'
    import { Plagiarism } from '../../../api'

    export default {
        name: 'plagiarism-results-section',

        components: { PopupSection, CharonSelect, PlagiarismSimilaritiesTable },

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

        watch: {
            charon() {
                this.fetchSimilarities()
            },
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
