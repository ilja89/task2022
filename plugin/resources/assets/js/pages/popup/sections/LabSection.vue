<template>
    <div class="lab">
        <div class="card  has-padding">
            <table class="table  is-fullwidth  is-striped  submission-counts__table">
                <thead>
                <tr>
                    <th v-on:click="sortTable('name')" class="sortable">Name</th>
                    <th v-on:click="sortTable('date')" class="sortable">Date</th>
                    <th v-on:click="sortTable('time')" class="sortable">Time</th>
                    <th v-on:click="sortTable('teachers')" class="sortable">Teachers</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="lab in labs">
                    <th>{{getDayTimeFormat(lab.start.time)}}</th>
                    <th>{{getNiceDate(lab.start.time)}}</th>
                    <th>{{getNiceTime(lab.start.time)}} - {{getNiceTime(lab.end.time)}}</th>
                    <th>
                        <b class="teachers" v-for="teacher in lab.teachers">{{teacher.full_name}}<b
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
            <button v-on:click="addNewLabSessionClicked" class="new_lab_button">NEW</button>
        </div>
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

<style scoped>

    hr {
        background-color: black;
        margin: 0
    }

    .font {
        font-size: 2vw;
        font-weight: 600;
    }

    .section {
        background-color: #d7dde4;
        border-style: solid;
        margin-bottom: 2vw;
    }

    .btn {
        float: right;
        border-style: none;
    }

    .inBoxButton {
        background-color: #d7dde4;
    }

    .new_lab_button {
        margin: 6px;
        margin-right: 0px;
        border: none;
        background-color: #44d244;
        font-size: 18px;
        float: right;
        cursor: pointer;
        padding: 7px;
        color: white;
        padding-left: 14px;
        padding-right: 14px;
    }

    .new_lab_button:hover {
        background-color: #37ab37;
    }

    table {
        background-color: papayawhip;
        color: #fff;
        border-radius: 2px;
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        max-width: 100%;
        padding: 0 24px;
        text-align: left !important;
        border: 2px solid;
        border-color: black;
    }

    .sortable {
        cursor: pointer;
        outline: 0;
        color: brown;
        font-weight: 580;
        font-size: 16px;
    }

    .sortable:active {
        color: coral;
    }

    th {
        padding: 0 24px;
        line-height: 45px;
    }

    tr {
        border: solid;
        border-width: 1px 0;
        border-color: #2b666c;
    }

    .clickable {
        cursor: pointer;
    }

    .edit_lab {
        color: #4f5f6f;
    }

    .edit_lab:hover {
        color: #f7e350;
    }

    .edit_lab:active {
        color: #f7e350;
    }

    .delete_lab {
        color: #4f5f6f;
    }

    .delete_lab:hover {
        color: red;
    }

    .delete_lab:active {
        color: red;
    }

    a {
        color: black;
    }

    .teachers {
        line-height: 2;
    }
</style>
