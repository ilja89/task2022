<template>

    <v-app-bar
            app
            dark
            id="core-toolbar">

        <v-app-bar-nav-icon/>

        <v-toolbar-title>
            {{ getCourseName() }}
        </v-toolbar-title>

        <v-spacer/>

        <v-toolbar-items>

            <div class="ttu-logo"></div>

            <student-search @student-was-changed="onStudentChanged"/>

            <md-icon @click="onRefreshClicked">refresh</md-icon>

            <extra-options @submission-was-added="onSubmissionAdded"/>

        </v-toolbar-items>
    </v-app-bar>

</template>

<script>
    import {StudentSearch, ExtraOptions} from "../partials";
    import {mapState, mapGetters} from "vuex";
    import VueTippy, {TippyComponent} from "vue-tippy";

    export default {
        components: {StudentSearch, ExtraOptions, TippyComponent},
        computed: {
            ...mapState(["student"]),
        },
        methods: {
            onRefreshClicked() {
                VueEvent.$emit("refresh-page");
            },

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