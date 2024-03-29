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

        <v-alert :value="alert" border="left" color="error" outlined>
            <v-row align="center" justify="space-between">
                <v-col class="grow">
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    Are you sure you want to delete this registration?
                    ({{this.item.student_name}}, {{this.item.lab_name}})
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="deleteRegistration">Yes
                    </v-btn>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="alert=false">No</v-btn>
                </v-col>
            </v-row>
        </v-alert>

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
                <v-btn class="ma-2" small tile outlined color="primary" @click="submissionClicked(item)" block>
                    {{ getSubmissionName(item) }}
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
                <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
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
import Submission from "../../../api/Submission";

export default {
    components: {Multiselect},
    data() {
        return {
            alert: false,
            item: Object,
            search: '',
            all_progress_types: ['Waiting', 'Defending', 'Done'],
            defense_list_headers: [
                {text: 'Nr. in queue', value: 'queue_nr', align: 'start'},
                {text: 'Lab', value: 'lab_name'},
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

            this.$router.push(this.getSubmissionRouting(submission.submission_id))
        },

        promptDeletionAlert(item) {
            this.alert = true
            this.item = item
        },

        deleteRegistration() {
            Defense.deleteStudentRegistration(this.item.charon_id, this.item.student_id, this.item.charon_defense_lab_id, this.item.submission_id, () => {
                VueEvent.$emit('show-notification', "Registration successfully deleted", 'danger')
                this.alert = false
                const index = this.findWithAttr(this.defenseList, "id", this.item.id);
                if (index > -1) {
                    this.defenseList.splice(index, 1);
                }
                this.item = Object
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
        },

        getSubmissionName(submission) {
            let name = "-";
            this.charons.forEach(charon => {
                if (charon.id === submission.charon_id) {
                    name = charon.name
                }
            });
            return name;
        },

        addQueueNumbersToDefenseList() {
            if (this.defenseList && this.defenseList.length) {
                let offset = 0;
                let i = 0;
                while (++i <= this.defenseList.length) {
                    this.defenseList[i - 1]['queue_nr'] = i - offset;
                    if (this.defenseList[i] && this.defenseList[i - 1]['lab_id'] !== this.defenseList[i]['lab_id']) {
                        offset = i.valueOf();
                    }
                }
            }
        }
    },
    computed: {
        ...mapState([
            'teacher', 'course', 'charons'
        ]),

        isSessionActive() {
            return this.teacher != null
        },

        defense_list_table() {
            this.addQueueNumbersToDefenseList();

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
