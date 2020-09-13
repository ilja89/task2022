<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Teachers</v-card-title>
        </v-card>

        <teacher-section/>
    </div>
</template>

<script>
    import {TeacherSection} from '../sections'

    export default {
        name: "teachers-page",

        components: {TeacherSection}
    }
</script>
