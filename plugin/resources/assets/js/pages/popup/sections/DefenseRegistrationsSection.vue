<template>
    <popup-section
            title="Code showing registrations"
            subtitle="Here are all the registrations for code showing."
    >
        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <div class="helper">
                    After
                </div>
                <div class="datepick">
                    <datepicker :datetime="after"></datepicker>
                    <input type="hidden" :value="after">
                </div>
                <div class="helper">
                    Before
                </div>
                <div class="datepick">
                    <datepicker :datetime="before"></datepicker>
                    <input type="hidden" :value="before">
                </div>
                <div class="helper">
                    Filter by teacher name
                </div>
                <div>
                    <multiselect v-model="filter_teacher" :options="teachers" label="name" placeholder="Select teachers"
                                 trackBy="id" :clear-on-select="true" class="multiselect__width">
                    </multiselect>
                    <br>
                </div>
                <div class="helper">
                    Filter by progress
                </div>
                <div>
                    <multiselect v-model="filter_progress" :options="all_progress_types"
                                 placeholder="Select progress"
                                 :clear-on-select="true" class="multiselect__width">
                    </multiselect>
                    <br>
                </div>
                <v-btn class="ma-2" tile outlined color="primary"
                       @click="apply(after.time, before.time, filter_teacher, filter_progress)">Apply
                </v-btn>
                <v-card class="mx-auto" outlined light raised>
                    <v-container class="spacing-playground pa-3" fluid>
                        <table class="table  is-fullwidth  is-striped  submission-counts__table">
                            <thead>
                            <tr>
                                <th>
                                    Date and time
                                </th>
                                <th>
                                    Student name
                                </th>
                                <th>
                                    Duration
                                </th>
                                <th>
                                    Teacher
                                </th>
                                <th>
                                    Submission
                                </th>
                                <th>
                                    Progress
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="defense in defenseList">
                                <td>{{defense.choosen_time}}</td>
                                <td>{{defense.student_name}}</td>
                                <td>{{getFormattedDuration(defense.defense_duration)}}</td>
                                <td>{{defense.teacher.firstname}} {{defense.teacher.lastname}}</td>
                                <td>
                                    <router-link :to="getSubmissionRouting(defense.submission_id)">Go to submission
                                    </router-link>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="dropbtn">{{defense.progress}}</button>
                                        <div id="dropdown-content" class="dropdown-content">
                                            <a v-on:click="saveProgress(defense.id, 'Waiting')">Waiting</a>
                                            <a v-on:click="saveProgress(defense.id, 'Defending')">Defending</a>
                                            <a v-on:click="saveProgress(defense.id, 'Done')">Done</a>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </v-container>
                </v-card>
            </v-container>
        </v-card>
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
                after: {time: null},
                before: {time: null},
                filter_teacher: {id: -1},
                filter_progress: null,
                all_progress_types: ['Waiting', 'Defending', 'Done']
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
        }
    }
</script>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 10px 10px !important;
    }


</style>
