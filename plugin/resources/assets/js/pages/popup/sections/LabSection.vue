<template>
    <div class="lab">
        <v-card
                class="mx-auto"
                outlined
                hover
                light
                raised
                shaped
        >
            <table class="table  is-fullwidth  is-striped  submission-counts__table">
                <thead>
                <tr>
                    <th v-on:click="sortTable('name')">Name</th>
                    <th v-on:click="sortTable('date')">Date</th>
                    <th v-on:click="sortTable('time')">Time</th>
                    <th v-on:click="sortTable('teachers')">Teachers</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="lab in labs">
                    <th>{{getDayTimeFormat(lab.start.time)}}</th>
                    <th>{{getNiceDate(lab.start.time)}}</th>
                    <th>{{getNiceTime(lab.start.time)}} - {{getNiceTime(lab.end.time)}}</th>
                    <th>
                        <b v-for="teacher in lab.teachers">{{teacher.full_name}}<b
                                v-if="lab.teachers[lab.teachers.length - 1] !== teacher">, <br></b>
                        </b>
                    </th>
                    <th>
                        <button v-on:click="editLabClicked(lab)">Edit</button>
                        <button v-on:click="deleteLabClicked(lab)">Delete</button>
                    </th>
                </tr>
                </tbody>
            </table>

            <v-btn class="ma-2" tile outlined color="success" v-on:click="addNewLabSessionClicked">Add</v-btn>
        </v-card>
    </div>
</template>

<script>
    import {mapActions, mapState} from "vuex";
    import Lab from "../../../api/Lab";

    export default {
        name: "LabSection.vue",
        props: {
            labs: {required: true}
        },
        data() {
            return {
                previous_param: null,
                current_param: null
            }
        },

        computed: {

            ...mapState([
                'course'
            ]),
        },

        methods: {
            ...mapActions(["updateLab", "updateLabToEmpty"]),

            addNewLabSessionClicked() {
                this.updateLabToEmpty()
                window.location = "popup#/labsForm";
            },
            getNiceTime(time) {
                let mins = time.getMinutes().toString();
                if (mins.length == 1) {
                    mins = "0" + mins;
                }
                return time.getHours() + ":" + mins
            },
            getNiceDate(date) {
                let month = (date.getMonth() + 1).toString();
                if (month.length == 1) {
                    month = "0" + month
                }
                return date.getDate() + '.' + month + '.' + date.getFullYear()
            },
            getDayTimeFormat(date) {
                let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                return daysDict[date.getDay()] + date.getHours();
            },
            editLabClicked(lab) {
                this.updateLab({lab})
                window.location = "popup#/labsForm";
            },
            deleteLabClicked(lab) {
                Lab.delete(this.course.id, lab.id, () => {
                    window.location.reload();
                    VueEvent.$emit('show-notification', 'Lab deleted!')
                })
            },
            sortTable(param) {
                this.current_param = param
                if (this.previous_param === this.current_param) {
                    this.labs.reverse();
                } else {
                    this.labs.sort(this.compare);
                }
            },
            compare(a, b) {
                let stringA, stringB
                if (this.current_param === 'date') {
                    stringA = a.start.time
                    stringB = b.start.time
                }
                if (this.current_param === 'name') {
                    stringA = a.start.time.getDay().toString() + a.start.time.getHours().toString()
                    stringB = b.start.time.getDay().toString() + b.start.time.getHours().toString()
                }
                if (this.current_param === 'time') {
                    stringA = a.start.time.getHours()
                    stringB = b.start.time.getHours()
                }
                if (this.current_param === 'teachers') {
                    stringA = a.teachers.length
                    stringB = b.teachers.length
                }

                let comparison = 0;
                if (stringA > stringB) {
                    comparison = 1;
                } else if (stringA < stringB) {
                    comparison = -1;
                } else if (this.current_param === 'time') {
                    stringA = a.end.time.getHours()
                    stringB = b.end.time.getHours()
                    if (stringA > stringB) {
                        comparison = 1;
                    } else if (stringA < stringB) {
                        comparison = -1;
                    }
                }

                this.previous_param = this.current_param
                return comparison;
            },
        }
    }


</script>
