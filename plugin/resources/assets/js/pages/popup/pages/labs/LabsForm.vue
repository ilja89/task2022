<template>
    <div>
        <lab-info-section :lab_given="lab" :teachers="teachers"></lab-info-section>
        <add-multiple-labs-section :lab="lab"></add-multiple-labs-section>
        <div class="btn-container btn-container-left">
            <button v-on:click="saveClicked" class="btn-labs btn-save-labs">Save</button>
        </div>

        <div class="btn-container btn-container-right">
            <router-link to="/labs"><button class="btn-labs btn-cancel-labs">Cancel</button></router-link>
        </div>

    </div>
</template>

<script>
    import LabInfoSection from "./sections/LabInfoSection";
    import AddMultipleLabsSection from "./sections/AddMultipleLabsSection";
    import {mapState} from "vuex";
    import Lab from "../../../../api/Lab";
    import User from "../../../../api/User";
    import Course from "../../../../api/Course";

    export default {

        components: { LabInfoSection, AddMultipleLabsSection },

        data() {
            return {
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
                    if (giveStart.toString().includes('GMT+0300')) {
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
                    Lab.save(this.course.id, this.lab.start.time, this.lab.end.time, chosen_teachers, this.lab.weeks, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab saved!');
                    })
                }
            },
            cancelClicked() {
                window.location = "popup#/labs";
            },
            giveTeachersFullNames() {
                for (let i = 0; i < this.teachers.length; i++) {
                    this.teachers[i].full_name = this.teachers[i].firstname + ' ' + this.teachers[i].lastname
                }
            }
        },
        computed: {

            ...mapState([
                'lab',
                'course'
            ]),
        },
        mounted() {
            User.getTeachers(this.course.id, (response) => {
                this.teachers = response;
                this.giveTeachersFullNames();
            })
        }
    }
</script>
