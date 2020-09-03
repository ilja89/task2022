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

            <v-btn icon color="primary">
                <md-icon>search</md-icon>
            </v-btn>

            <div class="search-container">
                <autocomplete
                        :url="studentsSearchUrl"
                        anchor="fullname"
                        label=""
                        :on-select="onStudentChanged"
                        id="student-search"
                        placeholder="Student name (uniid@ttu.ee)"
                        :min="2"
                />
            </div>

            <v-btn icon color="primary" @click="clearClicked">
                <md-icon>clear</md-icon>
            </v-btn>

            <v-btn icon color="primary" @click="onRefreshClicked">
                <md-icon>refresh</md-icon>
            </v-btn>

            <extra-options @submission-was-added="onSubmissionAdded"/>

        </v-toolbar-items>
    </v-app-bar>

</template>

<script>
    import {ExtraOptions} from "../partials";
    import {mapState, mapGetters} from "vuex";
    import autocomplete from "vue2-autocomplete-js";

    export default {
        components: {ExtraOptions, autocomplete},
        computed: {
            ...mapGetters([
                'studentsSearchUrl',
            ]),
            ...mapState(["student"]),
        },
        methods: {
            clearClicked() {
                this.$children.forEach((child) => {
                    if (child.$options._componentTag === 'autocomplete') {
                        child.setValue('')
                    }
                })
                document.getElementById('student-search').focus()
            },

            onRefreshClicked() {
                VueEvent.$emit("refresh-page");
            },

            onStudentChanged(student) {
                this.$router.push("/grading/" + student.id);
                VueEvent.$emit('student-was-changed', student)
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