<template>

    <v-app-bar
            app
            clipped-left
            dark
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

            <v-autocomplete
                    :url="studentsSearchUrl"
                    label=""
                    :items="items"
                    :loading="isLoading"
                    :search-input.sync="search"
                    :on-select="onStudentSelected"
                    id="student-search"
                    placeholder="Student name (uniid@ttu.ee)"
                    hide-no-data
                    hide-selected
            />

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
    import {studentsSearchUrl} from "../store/getters";

    export default {
        components: {ExtraOptions, autocomplete},
        computed: {
            ...mapGetters([
                'studentsSearchUrl',
            ]),
            ...mapState(["student"]),
        },
        data: () => ({
            entries: [],
            isLoading: false,
            model: null,
            search: null,
        }),
        watch: {
            search(val) {
                // Items have already been loaded
                if (this.items.length > 0) return

                // Items have already been requested
                if (this.isLoading) return

                this.isLoading = true

                // Lazily load input items
                fetch(this.studentsSearchUrl)
                    .then(res => res.json())
                    .then(res => {
                        const {count, entries} = res
                        this.count = count
                        this.entries = entries
                    })
                    .catch(err => {
                        VueEvent.$emit('show-notification', 'Error retrieving users.', 'danger')
                    })
                    .finally(() => (this.isLoading = false))
            },
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

            onStudentSelected(student) {
                this.$emit('student-was-changed', student)
            },
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