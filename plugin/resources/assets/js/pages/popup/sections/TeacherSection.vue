<template>
    <div>
        <!--        Because of some fuckery I can't use popup-section component here so copy pasta it is -->
        <v-card class="mx-auto mb-16">
            <v-system-bar color="blue"></v-system-bar>
            <v-toolbar flat>
                <v-toolbar-title>Teacher Overview</v-toolbar-title>
            </v-toolbar>
            <v-banner single-line sticky>
                Here is a list of all teachers and their general data
            </v-banner>
            <v-card-text class="grey lighten-4">
                <v-container class="spacing-playground pa-3" fluid>
                    <v-card-title v-if="teachers.length">
                        Teachers
                        <v-spacer></v-spacer>
                        <v-text-field
                                v-if="teachers.length"
                                v-model="search"
                                append-icon="search"
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
                            :headers="teachers_headers"
                            :items="teachers"
                            :search="search">

                        <template v-slot:item.actions="{ item }">
                            <v-btn class="ma-2" small tile outlined color="primary"
                                   @click="viewTeacherSpecifics(item)">
                                Details
                            </v-btn>
                        </template>
                        <template v-slot:no-results>
                            <v-alert :value="true" color="primary" icon="warning">
                                Your search for "{{ search }}" found no results.
                            </v-alert>
                        </template>
                    </v-data-table>
                </v-container>
            </v-card-text>
        </v-card>

        <v-card class="mx-auto mb-16">
            <v-system-bar color="blue"></v-system-bar>
            <v-toolbar flat>
                <v-toolbar-title>Teacher Specifics</v-toolbar-title>
            </v-toolbar>
            <v-banner single-line sticky>
                Here is some aggregated teacher data. Some fields are also visible to students. So fill them wisely.
            </v-banner>

            <v-dialog v-model="dialog" max-width="500px">
                <v-card>
                    <v-card-title>
                        <span class="headline">Edit item</span>
                    </v-card-title>

                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="12" lg="12">
                                    <v-text-field v-model="editedItem.teacher_comment"
                                                  label="Teacher comment"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="12" md="12" lg="12">
                                    <v-text-field v-model="editedItem.teacher_location"
                                                  label="Teacher location during the lab"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="closeDialog">Cancel</v-btn>
                        <v-btn color="blue darken-1" text @click="saveDialog">Save</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-card-text>
                <v-container class="spacing-playground pa-3" fluid v-if="teacher">
                    <v-data-table
                            v-if="teacher.length"
                            :headers="teacher_specifics_headers"
                            :items="teacher">

                        <template v-slot:item.actions="{ item }">
                            <v-btn class="ma-2" small tile outlined color="primary"
                                   @click="editItem(item)">
                                Edit
                            </v-btn>
                        </template>
                    </v-data-table>
                </v-container>

                <v-container class="spacing-playground pa-3" fluid v-else>
                    <v-card-title>
                        Select a teacher
                    </v-card-title>
                </v-container>
            </v-card-text>
        </v-card>

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
                teachers_headers: [
                    {text: 'First name', value: 'firstname', align: 'start'},
                    {text: 'Last name', value: 'lastname'},
                    {text: 'Defences', value: 'total_defences'},
                    {text: 'Actions', value: 'actions'},
                ],
                teacher_specifics_headers: [
                    {text: 'Lab start', value: 'start', align: 'start'},
                    {text: 'Lab end', value: 'end'},
                    {text: 'Teacher location', value: 'teacher_location'},
                    {text: 'Comment', value: 'teacher_comment'},
                    {text: 'Actions', value: 'actions'},
                ],
                editedItem: {
                    lab_id: 0,
                    teacher_id: 0,
                    teacher_location: "",
                    teacher_comment: "",
                },
                teachers: [],
                teacher: undefined,
                teacherData: undefined,
                dialog: false
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

        },

        created() {
            this.fetchTeachers()
        },

        methods: {

            fetchTeachers() {
                Teacher.getReport(this.courseId, teachers => {
                    this.teachers = teachers
                })
            },

            viewTeacherSpecifics(teacher) {
                this.teacher = teacher;
                Teacher.getByTeacher(this.courseId, teacher.id, teacher => {
                    this.teacher = teacher
                })
                Teacher.getTeacherAggregatedData(this.courseId, teacher.id, teacher => {
                    this.teacherData = teacher
                })
            },

            editItem(item) {
                this.editedIndex = this.teacher.indexOf(item)
                this.editedItem = Object.assign({}, item)
                this.dialog = true
            },

            closeDialog() {
                this.dialog = false
                this.$nextTick(() => {
                    this.editedItem = Object.assign({}, this.defaultItem)
                    this.editedIndex = -1
                })
            },

            saveDialog() {
                Object.assign(this.teacher[this.editedIndex], this.editedItem)
                Teacher.update(this.courseId, this.editedItem.lab_id, this.editedItem.teacher_id, this.editedItem)
                this.closeDialog()
            },

        },
    }
</script>
