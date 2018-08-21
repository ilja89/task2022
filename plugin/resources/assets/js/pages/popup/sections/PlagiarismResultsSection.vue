<template>
    <popup-section
        title="Plagiarism results"
        subtitle="Here are the results for the latest plagiarism check."
    >

        <template slot="header-right">
            <charon-select/>
        </template>

    </popup-section>
</template>

<script>
    import { mapState } from 'vuex'

    import { PopupSection } from '../layouts'
    import { CharonSelect } from '../partials'
    import { Plagiarism } from '../../../api'

    export default {
        name: 'plagiarism-results-section',

        components: { PopupSection, CharonSelect },

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
                    console.log('then', response)
                })
            },
        },
    }
</script>
