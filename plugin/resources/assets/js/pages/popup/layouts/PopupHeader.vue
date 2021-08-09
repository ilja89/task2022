<template>

    <v-app-bar
            app
            dense
            clipped-left
            id="core-toolbar">

        <v-app-bar-nav-icon @click="toggle_drawer">
            <md-icon>menu</md-icon>
        </v-app-bar-nav-icon>

        <v-toolbar-title class="title mr-6 mt-6" v-if="!is_mobile">
            {{ getCourseName() }}
        </v-toolbar-title>

        <v-autocomplete
                class="mt-8"
                v-model="model"
                :items="items"
                :loading="isLoading"
                :search-input.sync="search"
                color="primary"
                item-text="Description"
                item-value="API"
                label="Search student"
                clearable
                @change="onStudentSelected"
                placeholder="Student name (uniid@ttu.ee)"
                prepend-icon="mdi-database-search"
                return-object>
            <template v-slot:no-data>
                <v-list-item>
                    <v-list-item-title>
                        Search by <strong>first name</strong>,
                        <strong>last name</strong>, <strong>uni-id</strong> or <strong>id-code</strong>
                    </v-list-item-title>
                </v-list-item>
            </template>
        </v-autocomplete>

        <v-btn icon color="primary" :disabled="isLoading" @click="onRefreshClicked">
            <md-icon>refresh</md-icon>
        </v-btn>

        <extra-options/>

    </v-app-bar>

</template>

<script>
    import store from './../store/index'
    import ExtraOptions from "../partials/ExtraOptions";
    import {mapState, mapGetters, mapActions} from 'vuex'

    export default {
        components: {ExtraOptions},

        data: () => ({
            entries: [],
            isLoading: false,
            model: null,
            search: null,
        }),

        computed: {
            ...mapState([
                'is_mobile',
                'drawer',
                'student',
            ]),

            ...mapGetters([
                'courseId',
                'studentsSearchUrl'
            ]),

            fields() {
                if (!this.model) return []

                return Object.keys(this.model).map(key => {
                    return {
                        key,
                        value: this.model[key] || 'n/a',
                    }
                })
            },

            items() {
                return this.entries.map(entry => entry['fullname'])
            },
        },

        created() {
            this.fetchStudentFromRoute(this.$route.params.student_id);
        },

        methods: {
            ...mapActions([
                'fetchStudent'
            ]),

            onStudentChanged(student) {
                if (student !== null && student !== undefined) {
                    const courseId = parseInt(this.courseId);
                    const studentId = parseInt(student.id);

                    this.fetchStudent({courseId: courseId, studentId: studentId});
                    this.$router.push("/grading/" + studentId);
                }
            },

            getCourseName() {
                return window.course_name;
            },

            toggle_drawer() {
                setTimeout(() => store.state.drawer = !store.state.drawer, 50);
            },

            onRefreshClicked() {
                VueEvent.$emit("refresh-page");
            },

            onStudentSelected(student) {
                this.onStudentChanged(this.entries.find(x => x['fullname'] === student))
            },

            fetchStudentFromRoute(studentId) {
                if (studentId && !isNaN(studentId)) {
                    this.fetchStudent({courseId: parseInt(this.courseId), studentId: studentId}).then(() => {
                        VueEvent.$emit('student-loaded', studentId);
                    }).catch(() => {
                        VueEvent.$emit('show-notification', "User not found.", "error", 3000)
                    });
                }
            },
        },

        watch: {
            search(val) {
                if (this.isLoading || !val || val.length < 2) {
                    return
                }

                this.isLoading = true

                // Lazily load input items
                fetch(`${this.studentsSearchUrl}?q=${val}`)
                    .then(res => res.json())
                    .then(res => {
                        this.count = res.length
                        this.entries = res
                    })
                    .catch(err => {
                        VueEvent.$emit('show-notification', "Session has expired. Refreshing.", "error", 3000)
                        setInterval(function () {
                            location.reload();
                        }, 3000);
                    })
                    .finally(() => (this.isLoading = false))
            },

            // React to any route changes and act accordingly
            $route(to) {
                this.fetchStudentFromRoute(to.params.student_id)
            }
        },
    };

</script>
