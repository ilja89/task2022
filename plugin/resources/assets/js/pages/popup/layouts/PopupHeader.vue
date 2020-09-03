<template>

    <v-app-bar
            app
            dense
            clipped-left
            id="core-toolbar">

        <v-app-bar-nav-icon>
            <md-icon>menu</md-icon>
        </v-app-bar-nav-icon>

        <div class="ttu-logo"></div>

        <v-toolbar-title>
            {{ getCourseName() }}
        </v-toolbar-title>

        <v-spacer/>

        <v-toolbar-items>

            <student-search @student-was-changed="onStudentChanged"/>

        </v-toolbar-items>
    </v-app-bar>

</template>

<script>
    import {StudentSearch} from "../partials";
    import {mapState} from "vuex";

    export default {
        components: {StudentSearch},
        computed: {
            ...mapState(["student"]),
        },
        methods: {
            onStudentChanged(student) {
                this.$router.push("/grading/" + student.id);
            },

            getCourseName() {
                return window.course_name;
            }
        }
    };

</script>