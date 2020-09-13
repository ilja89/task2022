<template>
    <div>

        <popup-section title="Teacher overview"
                       subtitle="Here is a list of all teachers and their general data">
            <v-card class="mx-auto">
                <v-system-bar></v-system-bar>
                <v-toolbar flat>
                    <v-toolbar-title>Teacher Overview</v-toolbar-title>
                </v-toolbar>
                <v-banner
                        single-line
                        sticky
                >
                    See teachers and their defences.
                </v-banner>
                <v-card-text class="grey lighten-4">
                    <v-container class="spacing-playground pa-3" fluid>
                        <v-card-title v-if="teachers.length">
                            Teachers
                            <v-spacer></v-spacer>
                            <v-text-field
                                    v-if="teachers.length"
                                    v-model="search"
                                    label="Search"
                                    single-line
                                    hide-details>
                            </v-text-field>
                        </v-card-title>
                        <v-card-title v-else>
                            No Teachers for this course!
                        </v-card-title>

                        <v-data-table
                                v-if="teachers.length"
                                :headers="headers"
                                :items="teachers"
                                :search="search">

                            <template v-slot:item.actions="{ item }">
                                <v-btn class="ma-2" small tile outlined color="primary"
                                       @click="viewTeacherSpecifics(item)">
                                    Details
                                </v-btn>
                            </template>
                        </v-data-table>
                    </v-container>
                </v-card-text>
            </v-card>
        </popup-section>

        <popup-section title="Teacher specifics"
                       subtitle="Here is some aggregated teacher data" v-if="teacher">
            <v-card class="mx-auto" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>
                    {{teacher}}
                </v-container>
            </v-card>
        </popup-section>

    </div>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {PopupSection} from '../layouts';
    import Teacher from "../../../api/Teacher";

    export default {
        name: 'teacher-section',

        components: {PopupSection},

        data() {
            return {
                search: '',
                headers: [
                    {text: 'First name', value: 'firstname', align: 'start'},
                    {text: 'Last name', value: 'lastname'},
                    {text: 'Defences', value: 'total_defences'},
                    {text: 'Actions', value: 'actions'},
                ],
                teachers: [],
                teacher: undefined
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

        },

        mounted() {
            this.fetchTeachers()
            VueEvent.$on('refresh-page', this.fetchTeachers);
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off('refresh-page', this.fetchTeachers)
        },

        methods: {

            fetchTeachers() {
                Teacher.getReport(this.courseId, teachers => {
                    this.teachers = teachers
                })
            },

            viewTeacherSpecifics(teacher) {
                this.teacher = teacher;
            },

        },
    }
</script>
