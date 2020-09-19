<template>
    <popup-section
            title="Code showing registrations"
            subtitle="Here are all the registrations for code showing."
    >

        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <v-row>

                    <v-col cols="12" xs="12" sm="5" md="5" lg="5">
                        <div class="helper">
                            After
                        </div>
                        <div class="datepick">
                            <datepicker :datetime="after"></datepicker>
                            <input type="hidden" :value="after">
                        </div>
                    </v-col>

                    <v-col cols="12" xs="12" sm="5" md="5" lg="5">
                        <div class="helper">
                            Before
                        </div>
                        <div class="datepick">
                            <datepicker :datetime="before"></datepicker>
                            <input type="hidden" :value="before">
                        </div>
                    </v-col>

                    <v-col cols="12" xs="12" sm="5" md="2" lg="2">
                        <v-btn class="ma-2" tile outlined color="primary"
                               @click="apply(after.time, before.time, filter_teacher, filter_progress)">Apply
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
            <template v-slot:item.submission="{ item }">
                <router-link :to="getSubmissionRouting(item.submission_id)">Go to submission
                </router-link>
            </template>
            <template v-slot:item.progress="{ item }">
                <v-select
                        class="mx-auto ml-8"
                        dense
                        :items="all_progress_types"
                        v-model="item.progress"
                        @change="saveProgress(item.id, item.progress)"
                ></v-select>
            </template>
        </v-data-table>

    </popup-section>
</template>

<script>
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
                after: {time: null},
                before: {time: null},
                filter_teacher: {id: -1},
                filter_progress: null,
                all_progress_types: ['Waiting', 'Defending', 'Done'],
                defense_list_headers: [
                    {text: 'Date and time', value: 'choosen_time', align: 'start'},
                    {text: 'Student name', value: 'student_name'},
                    {text: 'Duration', value: 'formatted_duration'},
                    {text: 'Teacher', value: 'teacher_full_name'},
                    {text: 'Submission', value: 'submission'},
                    {text: 'Progress', value: 'progress'},
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
            saveProgress(defenseId, state) {
                Defense.saveDefenseProgress(this.course.id, defenseId, state, () => {
                    for (let i = 0; i < this.defenseList.length; i++) {
                        if (this.defenseList[i].id === defenseId) {
                            this.defenseList[i].progress = state
                            break
                        }
                    }
                })
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
                    container['teacher_full_name'] = `${registration.teacher.firstname} ${registration.teacher.lastname}`;

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
