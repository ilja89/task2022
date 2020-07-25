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

    export default {

        components: { LabInfoSection, AddMultipleLabsSection },

        data() {
            return {
                teachers: []
            }
        },

        methods: {
            saveClicked() {
                // send info to backend
                if (this.lab.id != null) {
                    // update lab
                    //console.log('update lab')
                    VueEvent.$emit('show-notification', 'Lab updated!');
                } else {
                    // save lab
                    //console.log('save lab')
                    Lab.save(this.course.id, this.lab.start.time, this.lab.end.time, () => {
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