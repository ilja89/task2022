<template>
    <div id="defenses app">
        <v-card class="mx-auto mb-16">
            <v-card-text class="grey lighten-4">
                <v-container class="spacing-playground pa-3" fluid>
                    <v-card-title v-if="defenseData.length">
                        {{translate('myRegistrationsText')}}
                        <v-spacer></v-spacer>

                        <v-text-field
                                v-if="defenseData.length"
                                v-model="search"
                                append-icon="search"
                                label="Search"
                                single-line
                                hide-details>
                        </v-text-field>
                    </v-card-title>

                    <v-card-title v-else>
                        {{translate('noRegistrationsText')}}
                    </v-card-title>

                    <v-data-table
                            :headers="headers"
                            :items="defenseData"
                            :search="search"
                            class="elevation-1"
                            single-line
                            multi-sort
                            :defenseData="defenseData"
                            :student_id="student_id"
                            :charon="charon"
                    >
                        <template slot="no-data">
                            <v-alert :value="true" style="text-align: center">
                                {{translate('tableNoRegistrationsText')}}
                            </v-alert>
                        </template>

                        <template v-slot:item.actions="{ item }">
                            <i @click="deleteItem(item)" class="fa fa-trash fa-lg" aria-hidden="true"></i>
                        </template>
                    </v-data-table>
                </v-container>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>

    import {Translate} from '../../../mixins';
    import Defense from "../../../api/Defense";

    export default {
        props: {
            defenseData: {required: true},
            student_id: {required: true},
            charon: {required: true},
        },
        mixins: [Translate],

        name: "StudentDefenses",

        data() {
            return {
                search: '',
                singleSelect: false,
                dialog: false,
                headers: [
                    {text: this.translate("charonText"), align: 'start', value: 'name'},
                    {text: this.translate("timeText"), value: 'choosen_time'},
                    {text: this.translate("teacherText"), value: 'teacher'},
                    {text: this.translate("locationText"), value: 'teacher_location'},
                    {text: this.translate("commentText"), value: 'teacher_comment'},
                    {text: this.translate("actionsText"), value: 'actions', sortable: false},
                ]
            }
        },

        methods: {
            deleteItem(item) {
                if (this.dateValidation(item)) {
                    if (confirm(this.translate("registrationDeletionConfirmationText"))) {
                        this.deleteReg(item);
                    }
                } else {
                    VueEvent.$emit('show-notification', this.translate("registrationBeforeErrorText"), 'danger')
                }
            },

            dateValidation(item) {
                const today = new Date();
                const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
                const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                const dateTime = date + ' ' + time;
                let day1 = moment.utc(dateTime, 'YYYY-MM-DD  HH:mm:ss');
                let day2 = moment.utc(item['choosen_time'], 'YYYY-MM-DD  HH:mm:ss');
                return day2.diff(day1, 'hours') >= 2;
            },

            deleteReg(defense_lab_item) {
                Defense.deleteStudentRegistration(this.charon.id, this.student_id, defense_lab_item['defense_lab_id'], defense_lab_item['submission_id'], (xs) => {
                    const index = this.defenseData.indexOf(defense_lab_item);
                    if (index > -1) {
                        this.defenseData.splice(index, 1)
                        VueEvent.$emit('show-notification', 'Deleted ' + xs + ' items successfully!', 'primary')
                    }
                    this.dialog = false
                })
            },
        },

        computed: {},

    }
</script>

<style>

    .v-application--wrap {
        min-height: 1vh !important;
    }


</style>