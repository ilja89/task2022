<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Lab settings</v-card-title>
        </v-card>

        <popup-section title="Edit Lab Settings"
                       subtitle="Here are the specifics for each Charon.">
            <lab-info-section :lab_given="lab" :charons="charons" :teachers="teachers"></lab-info-section>

            <add-multiple-labs-section :lab="lab"></add-multiple-labs-section>

            <v-btn class="ma-2" tile outlined color="primary" @click="saveClicked">
                Save
            </v-btn>

            <v-btn class="ma-2" tile outlined color="error" @click="cancelClicked">
                Cancel
            </v-btn>
        </popup-section>
    </div>
</template>

<script>
    import {PopupSection} from '../../layouts/index'
    import LabInfoSection from "./sections/LabInfoSection";
    import AddMultipleLabsSection from "./sections/AddMultipleLabsSection";
    import {mapState} from "vuex";
    import Lab from "../../../../api/Lab";
    import Teacher from "../../../../api/Teacher";
    import Charon from "../../../../api/Charon";

    export default {

        components: {LabInfoSection, AddMultipleLabsSection, PopupSection},

        data() {
            return {
                charons: [],
                teachers: []
            }
        },

        methods: {
            saveClicked() {
                if (!this.lab.start.time || !this.lab.end.time) {
                    VueEvent.$emit('show-notification', 'Please fill all the required fields.', 'danger');
                    return
                }
                let chosen_teachers = []
                if (this.lab.teachers !== undefined) {
                    for (let i = 0; i < this.lab.teachers.length; i++) {
                        chosen_teachers.push(this.lab.teachers[i].id)
                    }
                }
                // send info to backend
                if (this.lab.id != null) {
                    // update lab
                    let giveStart = this.lab.start.time
                    let giveEnd = this.lab.end.time
                    if (giveStart.toString().includes('GMT')) {
                        let num = giveStart.toString().substring(giveStart.toString().indexOf('GMT') + 4,
                            giveStart.toString().indexOf('GMT') + 6)
                        if (giveStart.toString().includes('GMT+')) {
                            giveStart = new Date(giveStart.setHours(giveStart.getHours() + parseInt(num)))
                        }
                        if (giveStart.toString().includes('GMT-')) {
                            giveStart = new Date(giveStart.setHours(giveStart.getHours() - parseInt(num)))
                        }
                    }
                    if (giveEnd.toString().includes('GMT+0300')) {
                        let num = giveEnd.toString().substring(giveEnd.toString().indexOf('GMT') + 4,
                            giveEnd.toString().indexOf('GMT') + 6)
                        if ((giveEnd.toString().includes('GMT+'))) {
                            giveEnd = new Date(giveEnd.setHours(giveEnd.getHours() + parseInt(num)))
                        }
                        if (giveEnd.toString().includes('GMT-')) {
                            giveEnd = new Date(giveEnd.setHours(giveEnd.getHours() - parseInt(num)))
                        }
                    }
                    Lab.update(this.course.id, this.lab.id, giveStart, giveEnd, chosen_teachers, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab updated!');
                    })
                } else {
                    // save lab
                    Lab.save(this.course.id, this.lab.start.time, this.lab.end.time, chosen_teachers, this.charons, this.lab.weeks, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab saved!');
                    })
                }
            },
            cancelClicked() {
                window.location = "popup#/labs";
            },
        },
        computed: {

            ...mapState([
                'lab',
                'course'
            ]),
        },

        created() {
            Teacher.getAllTeachers(this.course.id, (response) => {
                this.teachers = response;
            })

            Charon.all(this.course.id, (response) => {
                this.charons = response;
            })
        }
    }
</script>
