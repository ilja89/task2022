<template>
    <div>
        <page-title title="Labs"></page-title>
        <LabSection v-bind:labs="labs"/>
    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import {
        LabSection,
    } from '../sections'
    import {mapState} from "vuex";
    import Lab from "../../../api/Lab";
    import User from "../../../api/User";

    export default {
        name: "labs-page",
        data() {
            return {
                labs: {}
            }
        },

        components: {
            PageTitle, LabSection
        },
        mounted() {
            Lab.all(this.course.id, response => {
                this.labs = response;
                this.formatLabs();
            })
        },
        computed: {

            ...mapState([
                'lab',
                'course'
            ]),
        },
        methods: {
            formatLabs() {
                for (let i = 0; i < this.labs.length; i++) {
                    let save_start = this.labs[i].start
                    this.labs[i].start = {time: new Date(save_start)}
                    let save_end = this.labs[i].end
                    this.labs[i].end = {time: new Date(save_end)}
                    User.getTeachersInLab(this.course.id, this.labs[i].id, response => {
                        this.labs[i].teachers = this.getFullNamesForTeachers(response);
                    })
                }
            },
            getTeachersInThisLab(labId) {
                let teachers = []
                User.getTeachersInLab(this.course.id, labId, response => {
                    teachers = response;
                    this.getFullNamesForTeachers(teachers)
                    console.log(teachers)
                })
                return teachers;
            },
            getFullNamesForTeachers(teachers) {
                for (let i = 0; i < teachers.length; i++) {
                    teachers[i].full_name = teachers[i].firstName + ' ' + teachers[i].lastName
                }
                return teachers
            }

        }
    }
</script>
