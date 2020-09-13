<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Labs</v-card-title>
        </v-card>

        <lab-section v-bind:labs="labs"/>
    </div>

</template>

<script>
    import {LabSection} from '../sections'
    import {mapState} from "vuex";
    import Lab from "../../../api/Lab";

    export default {
        name: "labs-page",
        data() {
            return {
                labs: {},
                labs_countdown: null
            }
        },

        components: {LabSection},
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
                this.labs_countdown = labs.length
                for (let i = 0; i < labs.length; i++) {
                    let save_start = labs[i].start
                    labs[i].start = {time: new Date(save_start)}
                    let save_end = labs[i].end
                    labs[i].end = {time: new Date(save_end)}
                    this.getFullNamesForTeachers(labs[i].teachers)
                    then(labs)
                }
            },
            getFullNamesForTeachers(teachers) {
                for (let i = 0; i < teachers.length; i++) {
                    teachers[i].full_name = teachers[i].firstName + ' ' + teachers[i].lastName
                }
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
