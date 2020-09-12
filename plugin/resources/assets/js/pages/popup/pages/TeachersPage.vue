<template>
    <div>
        <page-title title="Teacher Overview"></page-title>

        <teacher-section></teacher-section>
    </div>
</template>

<script>
    import {PageTitle} from '../partials'
    import {TeacherSection} from '../sections'

    export default {
        name: "teachers-page",

        components: {
            PageTitle, TeacherSection
        }
    }
</script>
