<template>
    <div>
        <page-title :title="'Defense registrations'"></page-title>
        <defense-registrations-section :defense-list="defenseList" :apply="apply" :teachers="teachers"></defense-registrations-section>
    </div>
</template>

<script>

    import PageTitle from "../partials/PageTitle";
    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";
    import {mapState} from "vuex";
    import Defense from "../../../api/Defense";
    import User from "../../../api/User";

    export default {
        name: "defense-registrations-page",
        components: { PageTitle, DefenseRegistrationsSection },
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
            User.getTeachers(this.course.id, response => {
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
