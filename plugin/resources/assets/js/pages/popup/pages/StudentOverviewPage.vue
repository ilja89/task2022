<template>
    <div class="student-overview-container">

        <v-card class="mb-16 pl-4">
            <v-card-title>Student overview</v-card-title>
        </v-card>

        <popup-section title="Students"
                       subtitle="Here is a list of all students for this course.">

            <v-card-title v-if="students.length">
                Students
                <v-spacer></v-spacer>
                <v-text-field
                    v-if="students.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
                </v-text-field>
            </v-card-title>
            <v-card-title v-else>
                No Students for this course!
            </v-card-title>

            <v-data-table
                v-if="students.length"
                :headers="students_headers"
                :items="students"
                :search="search">
                <template v-slot:no-results>
                    <v-alert :value="true" color="primary" icon="warning">
                        Your search for "{{ search }}" found no results.
                    </v-alert>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn class="ma-2" small tile outlined color="primary" @click="studentDetails(item.id)">Details
                    </v-btn>
                </template>
            </v-data-table>

        </popup-section>

    </div>
</template>

<script>
import {PopupSection} from '../layouts'
import {User} from "../../../api";
import {mapGetters} from "vuex";

export default {

    components: {PopupSection},

    data() {
        return {
            alert: false,
            search: '',
            students: [],
            charons: [],
            students_headers: [
                {text: 'Full name', value: 'fullname', align: 'start'},
                {text: 'Uni-id', value: 'username'},
                {text: 'Actions', value: 'actions'}
            ]
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        studentName() {
            return this.student ? this.student.firstname + ' ' + this.student.lastname : "Student"
        }
    },

    methods: {
        studentDetails(id) {
            this.$router.push({name: 'student-details', params: {student_id: id}})
        }
    },

    created() {
        User.getStudentsInCourse(this.courseId, students => {
            this.students = students;
        })
    },

    metaInfo() {
        return {
            title: 'Student overview page'
        }
    }
}
</script>

<style lang="scss">

.student-overview-card {
    padding: 25px;
}

.b1l {
    padding: 0;
    width: 24px;
    min-width: 24px;
}

.student-overview-card {
    th {
        padding: 0.75em;

        img {
            margin-right: 15px;
        }
    }

    td {
        padding: 0.75em;
    }
}

</style>
