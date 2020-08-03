<template>
    <div>
        <page-title title="Labs"></page-title>
        <LabSection v-bind:labs="labs"/>
    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import { LabSection } from '../sections'
    import {mapState} from "vuex";
    import Lab from "../../../api/Lab";
    import User from "../../../api/User";

    export default {
        name: "labs-page",
        data() {
            return {
                labs: {},
                labs_countdown: null
            }
        },

        components: {
            PageTitle, LabSection
        },
        mounted() {
            Lab.all(this.course.id, response => {
                this.formatLabs(response, (done) => {
                    this.assignLabs(done)
                })
            })
        },
        computed: {

            ...mapState([
                'lab',
                'course'
            ]),
        },
        methods: {
            formatLabs(labs, then) {
                console.log(labs)
                this.labs_countdown = labs.length
                for (let i = 0; i < labs.length; i++) {
                    let save_start = labs[i].start
                    labs[i].start = {time: new Date(save_start)}
                    let save_end = labs[i].end
                    labs[i].end = {time: new Date(save_end)}
                    User.getTeachersInLab(this.course.id, labs[i].id, response => {
                        this.getFullNamesForTeachers(response, result => {
                            labs[i].teachers = result
                            then(labs)
                        })
                    })
                }
            },
            getFullNamesForTeachers(teachers, then) {
                for (let i = 0; i < teachers.length; i++) {
                    teachers[i].full_name = teachers[i].firstName + ' ' + teachers[i].lastName
                }
                then(teachers)
            },
            assignLabs(futureLabs) {
                this.labs_countdown--
                if (!this.labs_countdown) {
                    this.labs = futureLabs;
                }
            }

        }
    }
</script>
