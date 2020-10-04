<template>

    <div>
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
                <v-btn class="ma-2" small tile outlined color="primary" @click="submissionClicked(item)">
                    Go to submission
                </v-btn>
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
    </div>

</template>

<script>
    import Defense from "../../../api/Defense";
    import {mapState} from "vuex";
    import Multiselect from "vue-multiselect";

    export default {
        components: {Multiselect},
        data() {
            return {
                search: '',
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

            submissionClicked(submission) {
                if (submission.progress === 'Waiting' && this.isSessionActive) {
                    Defense.updateRegistration(this.course.id, submission.id, 'Defending', submission.teacher.id, () => {
                    })
                }

                window.location = this.getSubmissionRouting(submission.submission_id)
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
                'teacher', 'course'
            ]),

            isSessionActive() {
                return this.teacher != null
            },

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

</style>
