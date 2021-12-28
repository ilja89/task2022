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

        <alert-box-component v-if="updateAlert"
                             :eventName="'alert-box-active-registrations'"
                             :question="'What to do with your already active registration?'"
                             :text="defendingRegistration"
                             :buttonNames="updateRegistrationButtonNames">
        </alert-box-component>

        <alert-box-component v-if="sessionUpdateAlert"
                             :eventName="'alert-box-active-registrations-session'"
                             :question="'What to do with your already active registration?'"
                             :text="defendingRegistration"
                             :buttonNames="updateRegistrationButtonNames">
        </alert-box-component>

        <alert-box-component v-if="deleteAlert"
                             :eventName="'delete-registration-in-popup'"
                             :question="'Are you sure you want to delete this registration?'"
                             :text="registrationToDeleteText"
                             :buttonNames="deleteRegistrationButtonNames">
        </alert-box-component>

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
                    v-if="item.progress === 'Done'"
                    single-line
                    return-object
                    :items="item.lab_teachers"
                    item-text="fullname"
                    item-value="teacher"
                    v-model="item.teacher"
                    @change="updateRegistration(item)"
                    @focus="saveLastTeacherAndProgress(item.teacher, item.progress)"
                ></v-select>
                <v-select
                    class="mx-auto"
                    dense
                    v-else
                    clearable
                    single-line
                    return-object
                    :items="item.lab_teachers"
                    item-text="fullname"
                    item-value="teacher"
                    v-model="item.teacher"
                    @change="updateRegistrationTeacher(item)"
                    @focus="saveLastTeacherAndProgress(item.teacher, item.progress)"
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
                    @change="updateRegistrationCheckDefenses(item)"
                    @focus="saveLastTeacherAndProgress(item.teacher, item.progress)"
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
import AlertBoxComponent from "../../../components/partials/AlertBoxComponent";

export default {
    components: {Multiselect, AlertBoxComponent},
    data() {
        return {
            deleteAlert: false,
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
            lastProgress: '',
            lastTeacher: null,
            updateAlert: false,
            defendingRegistration: "",
            updateRegistrationButtonNames: ["Waiting", "Done", "Cancel"],
            registrationToUpdate: Object,
            registrationToDeleteText: "",
            deleteRegistrationButtonNames: ["Yes", "No"],
            sessionUpdateAlert: false,
        }
    },

    props: {
        defenseList: {required: true},
    },
    methods: {
        getSubmissionRouting(submissionId) {
            return '/submissions/' + submissionId
        },

        updateRegistrationTeacher(item) {
            const teacher_id = item.teacher ? item.teacher.id : null;
            if (item.progress === 'Defending' && teacher_id == null) {
                item.progress = 'Waiting';
                this.updateRegistration(item);
            } else if (item.progress === 'Defending') {
                Defense.getByTeacher(this.course.id, item.teacher ? item.teacher.id: null, item.lab_id, (registration) => {
                    const reg = registration.registration;
                    if (reg) {
                        this.defendingRegistration += reg.name + " - " + reg.firstname +
                            " " + reg.lastname + " - Progress: " + reg.progress
                        this.updateAlert = true;
                        this.registrationToUpdate = item;
                    } else {
                        this.updateRegistration(item);
                    }
                })
            } else {
                this.updateRegistration(item);
            }
        },

        updateRegistration(item) {
            const teacher_id = item.teacher ? item.teacher.id : null;
            Defense.updateRegistration(this.course.id, item.id, item.progress, teacher_id, (registration) => {
                if (registration == null) {
                    item.teacher = this.lastTeacher;
                    item.progress = this.lastProgress;
                } else if (teacher_id == null && (item.progress === 'Defending' || item.progress === 'Done')) {
                    item.teacher = registration.teacher;
                    item.progress = registration.progress;
                    VueEvent.$emit('show-notification', "Registration successfully updated", 'danger');
                }
            })
        },

        updateRegistrationCheckDefenses(item) {
            if (item.progress === 'Defending') {
                Defense.getByTeacher(this.course.id, item.teacher ? item.teacher.id: null, item.lab_id, (registration) => {
                    const reg = registration.registration;
                    if (reg) {
                        this.defendingRegistration += reg.name + " - " + reg.firstname +
                            " " + reg.lastname + " - Progress: " + reg.progress
                        this.updateAlert = true;
                        this.registrationToUpdate = item;
                    } else {
                        this.updateRegistration(item);
                    }
                })
            } else {
                this.updateRegistration(item);
            }
        },

        submissionClicked(registration) {
            if (registration.progress === 'Waiting' && this.isSessionActive) {
                Defense.getByTeacher(this.course.id, registration.teacher ? registration.teacher.id: null, registration.lab_id, (data) => {
                    const reg = data.registration;
                    if (reg) {
                        this.defendingRegistration += reg.name + " - " + reg.firstname +
                            " " + reg.lastname + " - Progress: " + reg.progress
                        this.sessionUpdateAlert = true;
                        this.registrationToUpdate = registration;
                    } else {
                        Defense.updateRegistration(this.course.id, registration.id, 'Defending', registration.teacher.id, () => {
                        })
                        this.$router.push(this.getSubmissionRouting(registration.submission_id))
                    }
                })
            } else {
                this.$router.push(this.getSubmissionRouting(registration.submission_id))
            }
        },

        promptDeletionAlert(item) {
            this.registrationToDeleteText = item.student_name + ' - ' + item.lab_name;
            this.deleteAlert = true
            this.item = item
        },

        deleteRegistration() {
            Defense.deleteStudentRegistration(this.item.charon_id, this.item.student_id,
                this.item.charon_defense_lab_id, this.item.submission_id, () => {
                VueEvent.$emit('show-notification', "Registration successfully deleted", 'danger')
                this.deleteAlert = false
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
        },

        saveLastTeacherAndProgress(teacher, progress) {
            this.lastTeacher = teacher;
            this.lastProgress = progress;
        },
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
    },

    mounted() {
        VueEvent.$on("alert-box-active-registrations", (buttonName) => {
            if (buttonName !== "Cancel") {
                Defense.updateRegistrationAndUndefendRegistrationsByTeacher(this.course.id,
                    this.registrationToUpdate.id, this.registrationToUpdate.progress, buttonName,
                    this.registrationToUpdate.lab_id, this.registrationToUpdate.teacher ? this.registrationToUpdate.teacher.id : null, _ => {
                        VueEvent.$emit('show-notification', "Registration successfully updated", 'danger');
                    })
            }
            VueEvent.$emit('refresh-defense-list');
            this.defendingRegistration = '';
            this.registrationToUpdate = Object;
            this.updateAlert = false;
        });

        VueEvent.$on("alert-box-active-registrations-session", (buttonName) => {
            if (buttonName !== "Cancel") {
                Defense.updateRegistrationAndUndefendRegistrationsByTeacher(this.course.id,
                    this.registrationToUpdate.id, 'Defending', buttonName,
                    this.registrationToUpdate.lab_id, this.registrationToUpdate.teacher ? this.registrationToUpdate.teacher.id : null, _ => {
                        this.$router.push(this.getSubmissionRouting(this.registrationToUpdate.submission_id))
                    })
            }
            this.defendingRegistration = '';
            this.registrationToUpdate = Object;
            this.sessionUpdateAlert = false;
        });

        VueEvent.$on("delete-registration-in-popup", (buttonName) => {
            if (buttonName === "Yes") {
                this.deleteRegistration();
            } else {
                this.deleteAlert = false;
            }
        });
    }
}
</script>

<style>

</style>
