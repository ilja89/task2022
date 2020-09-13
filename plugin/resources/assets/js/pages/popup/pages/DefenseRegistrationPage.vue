<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Registrations</v-card-title>
        </v-card>

        <defense-registrations-section :defense-list="defenseList" :apply="apply" :teachers="teachers"/>
    </div>

</template>

<script>

    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";
    import {mapState} from "vuex";
    import Defense from "../../../api/Defense";
    import Teacher from "../../../api/Teacher";

    export default {
        name: "defense-registrations-page",
        components: {DefenseRegistrationsSection},
        data() {
            return {
                defenseList: [],
                countDown: 0,
                teachers: []
            }
        },
        computed: {

            ...mapState([
                'course'
            ]),
        },
        mounted() {
            Defense.all(this.course.id, response => {
                this.defenseList = response
            })
            Teacher.getAllTeachers(this.course.id, response => {
                this.getNamesForTeachers(response, result => {
                    this.teachers = result
                })
            })
        },
        methods: {
            apply(after, before, filter_teacher, filter_progress) {
                Defense.filtered(this.course.id, after, before, filter_teacher.id, filter_progress, response => {
                    this.defenseList = response
                })
            },
            getNamesForTeachers(teachers, then) {
                for (let i = 0; i < teachers.length; i++) {
                    teachers[i].name = teachers[i].firstname + ' ' + teachers[i].lastname
                }
                then(teachers)
            }
        }
    }
</script>
