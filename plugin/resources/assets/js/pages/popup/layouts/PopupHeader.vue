<template>

    <v-app-bar
            app
            dense
            clipped-left
            id="core-toolbar">

        <v-app-bar-nav-icon @click="toggle_drawer">
            <md-icon>menu</md-icon>
        </v-app-bar-nav-icon>

        <v-toolbar-title v-if="!is_mobile">
            {{ getCourseName() }}
        </v-toolbar-title>

        <v-spacer/>

        <v-toolbar-items>

            <student-search @student-was-changed="onStudentChanged"/>

        </v-toolbar-items>
    </v-app-bar>

</template>

<script>
    import store from './../store/index'
    import {StudentSearch} from "../partials";
    import {mapState} from "vuex";

    export default {
        components: {StudentSearch},
        computed: {
            ...mapState([
                'is_mobile',
                'drawer',
                'student',
            ]),
        },
        methods: {
            onStudentChanged(student) {
                this.$router.push("/grading/" + student.id);
            },

            getCourseName() {
                return window.course_name;
            },

            toggle_drawer() {
                setTimeout(() => store.state.drawer = !store.state.drawer, 50);
            }
        }
    };

</script>