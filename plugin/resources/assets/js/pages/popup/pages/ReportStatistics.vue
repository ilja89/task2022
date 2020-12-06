<template>
    <div>
        <page-title title="Report & Statistics"></page-title>

        <report-all-students></report-all-students>
    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import { ReportAllStudents } from '../sections/';

    export default {
        components: { PageTitle, ReportAllStudents },
    };
</script>
