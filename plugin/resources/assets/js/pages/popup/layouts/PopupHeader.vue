<template>

    <v-app-bar
            app
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

            <student-search @student-was-changed="onStudentChanged" />

            <extra-options @submission-was-added="onSubmissionAdded"/>

        </v-toolbar-items>
    </v-app-bar>

</template>

<script>
    import {StudentSearch, ExtraOptions} from "../partials";
    import {mapState} from "vuex";

    export default {
        components: { StudentSearch, ExtraOptions },
        computed: {
            ...mapState(["student"]),
        },
        methods: {
            onStudentChanged(student) {
                this.$router.push("/grading/" + student.id);
            },

            onSubmissionAdded() {
                VueEvent.$emit("refresh-page");
            },
            getCourseName() {
                return window.course_name;
            }
        }
    };

</script>