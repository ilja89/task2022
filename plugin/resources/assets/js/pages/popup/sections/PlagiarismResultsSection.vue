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
    import { PopupSection } from '../layouts'
    import { CharonSelect } from '../partials'

    export default {
        name: 'plagiarism-results-section',
        components: { PopupSection, CharonSelect },
    }
</script>
