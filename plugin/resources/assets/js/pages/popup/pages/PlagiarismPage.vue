<template>
    <div>

        <page-title title="Plagiarism"></page-title>

        <plagiarism-results-section></plagiarism-results-section>

    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import { PlagiarismResultsSection } from '../sections'

    export default {
        name: 'plagiarism_page',
        components: { PageTitle, PlagiarismResultsSection },
    }
</script>
