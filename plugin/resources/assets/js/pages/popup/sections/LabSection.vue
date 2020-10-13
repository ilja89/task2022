<template>
    <popup-section
            title="Labs overview"
            subtitle="Here are the the labs where students can show their code.">
      <template slot="header-right">
        <v-btn class="ma-2" tile outlined color="primary" v-on:click="addNewLabSessionClicked">Add new</v-btn>
      </template>


      <v-alert :value="alert" border="left" color="error" outlined>
            <v-row align="center" justify="space-between">
                <v-col class="grow">
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    Are you sure you want to delete the lab?
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="deleteLab">Yes</v-btn>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="alert=false">No</v-btn>
                </v-col>
            </v-row>
        </v-alert>
        <v-card-title v-if="labs.length">
            Labs
            <v-spacer></v-spacer>
            <v-text-field
                    v-if="labs.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
            </v-text-field>
        </v-card-title>

        <v-card-title v-else>
            No Labs for this course!
        </v-card-title>

        <v-data-table
                v-if="labs.length"
                :headers="labs_headers"
                :items="labs_table"
                :search="search">

            <template v-slot:item.actions="{ item }">
                <v-btn class="ma-2" small tile outlined color="primary" @click="editLabClicked(item)">Edit
                </v-btn>
                <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
                    Delete
                </v-btn>
            </template>
            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>
        </v-data-table>

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import {mapActions, mapState} from "vuex";
    import Lab from "../../../api/Lab";

    export default {
        name: "lab-section",

        components: {PopupSection},

        props: {
            labs: {required: true}
        },

        data() {
            return {
                alert: false,
                lab_id: 0,
                search: '',
                labs_headers: [
                    {text: 'Name', value: 'nice_name', align: 'start'},
                    {text: 'Date', value: 'nice_date'},
                    {text: 'Time', value: 'nice_time'},
                    {text: 'Teachers', value: 'teacher_names'},
                    {text: 'Charons', value: 'charon_names'},
                    {text: 'Actions', value: 'actions'},
                ],
                previous_param: null,
                current_param: null
            }
        },

        computed: {

            ...mapState([
                'course'
            ]),

            labs_table() {
                return this.labs.map(lab => {
                    const container = {...lab};

                    container['nice_name'] = this.getDayTimeFormat(lab.start.time);
                    container['nice_date'] = this.getNiceDate(lab.start.time);
                    container['nice_time'] = `${this.getNiceTime(lab.start.time)} - ${this.getNiceTime(lab.end.time)}`;
                    container['teacher_names'] = lab.teachers.map(x => x.fullname).sort().join(', ')
                    container['charon_names'] = lab.charons.map(x => x.project_folder).sort().join(', ')

                    return container;
                });
            }
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

            promptDeletionAlert(lab) {
                this.alert = true
                this.lab_id = lab.id
            },

            deleteLab() {
                this.alert = false
                Lab.delete(this.course.id, this.lab_id, () => {
                    this.labs = this.labs.filter(x => x.id !== this.lab_id)
                    VueEvent.$emit('show-notification', 'Lab deleted!')
                })
            },
        }
    }

</script>
