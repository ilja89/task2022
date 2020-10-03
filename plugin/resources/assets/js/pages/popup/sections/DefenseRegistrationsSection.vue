<template>
    <popup-section
            title="Code showing registrations"
            subtitle="Here are all the registrations for code showing."
    >

        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <v-row>

                    <v-col cols="12" xs="12" sm="12" md="3" lg="3">
                        <div class="helper">
                            After
                        </div>
                        <div class="datepick">
                            <datepicker :datetime="after"></datepicker>
                            <input type="hidden" :value="after">
                        </div>
                    </v-col>

                    <v-col cols="12" xs="12" sm="12" md="3" lg="3">
                        <div class="helper">
                            Before
                        </div>
                        <div class="datepick">
                            <datepicker :datetime="before"></datepicker>
                            <input type="hidden" :value="before">
                        </div>
                    </v-col>

                    <v-col cols="12" xs="12" sm="4" md="2" lg="2">
                        <div class="helper">
                            Teacher name
                        </div>

                        <v-select
                                class="mx-auto"
                                dense
                                single-line
                                item-text="fullname"
                                item-value="id"
                                :items="teachers"
                                v-model="filter_teacher"
                        ></v-select>
                    </v-col>

                    <v-col cols="12" xs="12" sm="4" md="2" lg="2">
                        <div class="helper">
                            Progress
                        </div>

                        <v-select
                                class="mx-auto"
                                dense
                                :items="all_progress_types"
                                v-model="filter_progress"
                        ></v-select>
                    </v-col>

                    <v-col cols="12" xs="12" sm="4" md="2" lg="2">
                        <v-btn class="ma-2" tile outlined color="primary"
                               @click="apply(after.time, before.time, filter_teacher, filter_progress)">
                            Apply
                        </v-btn>
                    </v-col>

                </v-row>
            </v-container>
        </v-card>


        <v-card-title v-if="defenseList.length">
            Registrations
            <v-spacer></v-spacer>
            <v-text-field
                    v-if="defenseList.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
            </v-text-field>
        </v-card-title>
        <v-card-title v-else>
            No Registrations for this course!
        </v-card-title>

        <v-data-table
                v-if="defenseList.length"
                :headers="defense_list_headers"
                :items="defense_list_table"
                :search="search">

            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>

            <template v-slot:item.teacher="{ item }">
                <v-select
                        class="mx-auto"
                        dense
                        single-line
                        return-object
                        :items="teachers"
                        item-text="fullname"
                        item-value="teacher"
                        v-model="item.teacher"
                        @change="updateRegistration(item.id, item.progress, item.teacher.id)"
                ></v-select>
            </template>

            <template v-slot:item.submission="{ item }">
                <router-link :to="getSubmissionRouting(item.submission_id)">Go to submission
                </router-link>
            </template>

            <template v-slot:item.progress="{ item }">
                <v-select
                        class="mx-auto"
                        dense
                        :items="all_progress_types"
                        v-model="item.progress"
                        @change="updateRegistration(item.id, item.progress, item.teacher.id)"
                ></v-select>
            </template>

            <template v-slot:item.actions="{ item }">
                <v-btn class="ma-2" small tile outlined color="error" @click="deleteRegistration(item)">
                    Delete
                </v-btn>
            </template>
        </v-data-table>

    </popup-section>
</template>

<script>
    import moment from 'moment';
    import {PopupSection} from '../layouts/index'
    import Datepicker from "../../../components/partials/Datepicker";
    import Defense from "../../../api/Defense";
    import {mapState} from "vuex";
    import Multiselect from "vue-multiselect";

    export default {
        components: {Datepicker, Multiselect, PopupSection},
        data() {
            return {
                search: '',
                after: {time: `${moment().format("YYYY-MM-DD")} 00:00`},
                before: {time: null},
                filter_teacher: -1,
                filter_progress: null,
                all_progress_types: ['Waiting', 'Defending', 'Done'],
                defense_list_headers: [
                    {text: 'Date and time', value: 'choosen_time', align: 'start'},
                    {text: 'Student name', value: 'student_name'},
                    {text: 'Duration', value: 'formatted_duration'},
                    {text: 'Teacher', value: 'teacher'},
                    {text: 'Submission', value: 'submission'},
                    {text: 'Progress', value: 'progress'},
                    {text: 'Actions', value: 'actions'},
                ],

            }
        }, props: {
            defenseList: {required: true},
            apply: {required: true},
            teachers: {required: true}
        },
        methods: {
            getSubmissionRouting(submissionId) {
                return '/submissions/' + submissionId
            },
            updateRegistration(defense_id, state, teacher_id) {
                Defense.updateRegistration(this.course.id, defense_id, state, teacher_id, () => {
                    VueEvent.$emit('show-notification', "Registration successfully updated", 'danger')
                })
            },

            deleteRegistration(item) {
                Defense.deleteStudentRegistration(item.charon_id, item.student_id, item.charon_defense_lab_id, item.submission_id, () => {
                    VueEvent.$emit('show-notification', "Registration successfully deleted", 'danger')
                    const index = this.findWithAttr(this.defenseList, "charon_defense_lab_id", item.charon_defense_lab_id);
                    if (index > -1) {
                        this.defenseList.splice(index, 1);
                    }
                })
            },

            findWithAttr(array, attr, value) {
                for (let i = 0; i < array.length; i += 1) {
                    if (array[i][attr] === value) {
                        return i;
                    }
                }
                return -1;
            },

            getFormattedDuration(duration) {
                if (duration === null) {
                    return '-'
                }
                return duration + ' min'
            }
        },
        computed: {
            ...mapState([
                'course'
            ]),

            defense_list_table() {
                return this.defenseList.map(registration => {
                    const container = {...registration};
                    container['formatted_duration'] = this.getFormattedDuration(registration.defense_duration);
                    return container;
                });
            }
        }
    }
</script>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 10px 10px !important;
    }


</style>
