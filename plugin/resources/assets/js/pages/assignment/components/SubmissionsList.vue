<template>
    <v-app>
        <v-main>
            <v-snackbar multi-line v-model="notification.show"
                        :timeout="notification.timeout">
                {{ notification.text }}

                <template v-slot:action="{ attrs }">
                    <v-btn color="blue" text v-bind="attrs" @click="notification.show = false">
                        {{translate('closeText')}}
                    </v-btn>
                </template>
            </v-snackbar>


            <h2 class="title">{{ translate('submissionsText') }}
                <svg @click="refreshSubmissions()" class="svg-icon"
                     v-bind:class="this.refreshing? 'svg-icon rotating' : 'svg-icon'" viewBox="0 0 20 20">
                    <path fill="none"
                          d="M3.254,6.572c0.008,0.072,0.048,0.123,0.082,0.187c0.036,0.07,0.06,0.137,0.12,0.187C3.47,6.957,3.47,6.978,3.484,6.988c0.048,0.034,0.108,0.018,0.162,0.035c0.057,0.019,0.1,0.066,0.164,0.066c0.004,0,0.01,0,0.015,0l2.934-0.074c0.317-0.007,0.568-0.271,0.56-0.589C7.311,6.113,7.055,5.865,6.744,5.865c-0.005,0-0.01,0-0.015,0L5.074,5.907c2.146-2.118,5.604-2.634,7.971-1.007c2.775,1.912,3.48,5.726,1.57,8.501c-1.912,2.781-5.729,3.486-8.507,1.572c-0.259-0.18-0.618-0.119-0.799,0.146c-0.18,0.262-0.114,0.621,0.148,0.801c1.254,0.863,2.687,1.279,4.106,1.279c2.313,0,4.591-1.1,6.001-3.146c2.268-3.297,1.432-7.829-1.867-10.101c-2.781-1.913-6.816-1.36-9.351,1.058L4.309,3.567C4.303,3.252,4.036,3.069,3.72,3.007C3.402,3.015,3.151,3.279,3.16,3.597l0.075,2.932C3.234,6.547,3.251,6.556,3.254,6.572z"></path>
                </svg>
            </h2>

            <v-bottom-sheet v-model="isActive" persistent inset>
                <v-sheet height="80vh" class="px-4">

                    <v-card-text class="my-4 text-center title">
                        {{translate('registrationForText')}} {{this.charon['name']}}
                    </v-card-text>

                    <div class="register-lab-headers" style="margin-top: 2vh">
                        <h4>{{translate('chooseTeacherText')}}</h4>
                    </div>
                    <div class="labs-schedule">
                        <div class="row">
                            <div class="col-6 col-sm-4" v-if="this.charon['choose_teacher'] === 1"><label
                                    for="my-teacher"></label>
                                <input type="radio" v-model="selected" id="my-teacher"
                                       value="My teacher" name="labs-time" @click="changeTeacher('My teacher')">
                                {{translate('myTeacherText')}}
                            </div>
                            <div class="w-100 d-none d-md-block"></div>
                            <div class="col-6 col-sm-4">
                                <label for="another-teacher"></label>
                                <input type="radio" v-model="selected" id="another-teacher" value="Any teacher"
                                       name="labs-time" @click="changeTeacher('Any teacher')">
                                {{translate('anyTeacherText')}}
                            </div>
                        </div>
                    </div>
                    <div class="register-lab-headers" style="margin-top: 2vh">
                        <h4>{{translate('chooseTimeText')}}</h4>
                    </div>
                    <div class="labs-schedule">
                        <div class="text-center">
                            <multiselect v-model="value" :options="this.labs" :block-keys="['Tab', 'Enter']"
                                         @select="onSelect" :max-height="200"
                                         :custom-label="getLabList" :placeholder="translate('selectDayText')"
                                         label="start"
                                         track-by="start" :allow-empty="false">
                                <template slot="singleLabel" slot-scope="{ option }">{{ option.start }}</template>
                            </multiselect>

                            <multiselect style="margin-top: 30px" v-if="this.value != null" v-model="value_time"
                                         :max-height="200"
                                         :options="this.times" :placeholder="translate('selectTimeText')">
                            </multiselect>
                        </div>
                    </div>

                    <v-row class="my-4">
                        <v-btn
                                class="mt-6" text color="primary"
                                @click="sendData()">
                            {{translate('registerText')}}
                        </v-btn>

                        <v-btn
                                class="mt-6" text color="error"
                                @click="isActive = false">
                            {{translate('closeText')}}
                        </v-btn>
                    </v-row>

                </v-sheet>
            </v-bottom-sheet>

            <v-bottom-sheet v-model="isActiveDefenses" inset>
                <v-sheet height="80vh" class="px-4">

                    <v-card-text class="my-4 text-center title">
                        {{translate('allRegistrationsText')}}
                    </v-card-text>

                    <StudentDefenses
                            :defenseData="defenseData"
                            :student_id="student_id"
                            :charon="charon">
                    </StudentDefenses>

                    <v-row class="my-4">
                        <v-btn
                                class="mt-6" text color="error"
                                @click="isActiveDefenses = false">
                            {{translate('closeText')}}
                        </v-btn>
                    </v-row>

                </v-sheet>
            </v-bottom-sheet>

            <ul class="submissions-list">
                <template v-for="(submission, index) in submissions">
                    <li class="submission-row" :class="{ active: showingAdvanced(submission.id) }"
                        @click="toggleAdvanced(submission.id)" :key="index">

                    <span class="tag is-info"
                          :class="{registered: listStyle(submission.id), defended: defendedSubmission(submission)}">
                        {{ submissionString(submission) }}
                    </span>

                        <span class="submission-time">
                        {{ submission.created_at | date }}
                    </span>

                        <span class="dropdown-arrow">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path
                                d="M0 0h24v24H0z" fill="none"/></svg>
                    </span>

                        <span @click.stop="$emit('submission-was-activated', submission)">
                        <img style="min-width: 24px; min-height: 24px" width="24px" height="24px" src="pix/eye.png"
                             alt="eye">
                    </span>

                        <span @click="validateSubmission(submission)" @click.stop="showModalLabs(submission.id)">
                        <img style="min-width: 24px; min-height: 24px" width="24px" height="24px" src="pix/shield.png"
                             alt="shield">
                    </span>

                    </li>

                    <div v-if="showingAdvanced(submission.id)" class="results-list-container">
                        <ul class="results-list">
                            <li v-for="result in submission.results" class="result-row">
                        <span>
                            {{ getGrademapByResult(result).name }}
                        </span>
                                <span>
                            {{ result.calculated_result }} | {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}
                        </span>
                            </li>
                        </ul>
                    </div>

                </template>
            </ul>

            <div v-if="canLoadMore" class="has-text-centered">
                <button class="button is-primary load-more-button" @click="loadMoreSubmissions()">
                    {{translate('loadMoreText')}}
                </button>
            </div>
            <div class="has-text-centered">
            <span>
            <button class="button is-primary load-more-button" @click.stop="showStudentDefenses()">
                {{translate('myRegistrationsText')}}
            </button>
            </span>
            </div>
        </v-main>
    </v-app>
</template>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>

    .tag.is-info.registered {
        background-color: #faee0a;
    }

    .tag.is-info.defended {
        background-color: #66fa0a;
    }

    .svg-icon {
        width: 1em;
        height: 1em;
        vertical-align: text-bottom;
        cursor: pointer;
    }

    .svg-icon path,
    .svg-icon polygon,
    .svg-icon rect {
        fill: #03a9f4;
    }

    .svg-icon circle {
        stroke: #03a9f4;
        stroke-width: 1;
    }

    .text-center {
        text-align: center;
    }


    .rotating {
        animation-name: spin;
        animation-duration: 1000ms;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(-360deg);
        }
    }

</style>
<script>

    import {Translate} from '../../../mixins';
    import {Submission} from '../../../api';
    import Modal from '../../../components/partials/Modal.vue';
    import Datepicker from "../../../components/partials/Datepicker.vue";
    import {Multiselect} from "vue-multiselect";
    import StudentDefenses from "./StudentDefenses";

    export default {

        mixins: [Translate],
        components: {
            Modal, Datepicker, Multiselect, StudentDefenses
        },

        props: {
            grademaps: {required: true},
            charon_id: {required: true},
            student_id: {required: true},
        },

        data() {
            return {
                loaderVisible: 0,
                notification: {
                    text: '',
                    show: false,
                    type: 'success',
                    timeout: 1000,
                },

                my_teacher: true,
                value: null,
                value_time: null,
                advanced: [],
                submissions: [],
                index: 0,
                registered: false,
                defended: false,
                current_submission: 0,
                selected: 'Any teacher',
                cached_option: null,
                selected_lab: Object,
                canLoadMore: true,
                refreshing: false,
                isActive: false,
                isActiveDefenses: false,
                charon: '',
                teacher_options: [],
                times: [],
                labs: [],
                not_available_times: [],
                defenseData: [],
                submission_validation: false,
                array_to_show: []
            };
        },

        filters: {
            withoutTrailingZeroes(number) {
                return number.replace(/000$/, '');
            },

            date(date) {
                return window.moment(date, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD HH:mm");
            }
        },

        created() {
            this.initializeEventListeners()
        },

        methods: {
            initializeEventListeners() {
                VueEvent.$on('show-notification', (message, type = 'success', timeout = 2000) => {
                    this.showNotification(message, type, timeout)
                });
                VueEvent.$on('close-notification', _ => this.notification.show = false)
                VueEvent.$on('show-loader', _ => this.loaderVisible += 1)
                VueEvent.$on('hide-loader', _ => this.hideLoader())
            },

            showNotification(message, type, timeout = 2000) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type
                this.notification.timeout = timeout
            },

            hideLoader() {
                if (this.loaderVisible !== 0) {
                    this.loaderVisible--
                }
            },

            arrayDefenseTime() {
                if (this.cached_option != null) {
                    const option = this.cached_option;
                    this.times.length = 0;
                    let time = option['start'].split(' ')[0];
                    this.timeGenerator(option);
                    axios.get(`api/charons/${this.charon_id}/labs/unavailable?time=${time
                        }&my_teacher=${this.selected === "My teacher"
                        }&student_id=${this.student_id
                        }&lab_id=${option['id']
                        }&charon_id=${this.charon_id}`
                    ).then(result => {
                        this.not_available_times = result.data;
                        this.timeGenerator(option);
                    })
                }

            },

            timeGenerator(option) {
                let defense_duration = this.charon['defense_duration'];
                let startTime = moment(option['start'], 'YYYY-MM-DD HH:mm:ii')
                let endTime = moment(option['end'], 'YYYY-MM-DD HH:mm:ii');
                let curTime = moment();

                this.times = [];
                while (startTime < endTime) {
                    const time = startTime.format('HH:mm');
                    if (!this.not_available_times.includes(time) && curTime.isBefore(startTime)) {
                        this.times.push(time);
                    }
                    startTime.add(defense_duration, 'minutes');
                }
                if (this.not_available_times.length !== 0) {
                    this.times = this.times.filter(x => !this.not_available_times.includes(x));
                }
            },

            defendedSubmission(submission) {
                try {
                    const last = submission.results[submission.results.length - 1];
                    return parseFloat(last['calculated_result']) !== 0.0 && last['grade_type_code'] === 1001;
                } catch (e) {
                    return false
                }
            },

            getLabList({start}) {
                let date = `${start.split(' ')[0]}`;
                let time = `${start.split(' ')[1]}`;
                let time_return = time.split(':');
                return date + " " + time_return[0] + ":" + time_return[1];
            },

            listStyle(submissionId) {
                let test = this.defenseData.find(x => x.submission_id === submissionId);
                if (test != null) {
                    test = test['submission_id'];
                    return submissionId === test;
                }
            },

            forceRerender() {
                this.index += 1;
            },

            changeTeacher(teacher) {
                this.selected = teacher;
                this.arrayDefenseTime();
            },

            onSelect(option) {
                this.cached_option = option;
                this.arrayDefenseTime();
            },

            showModalLabs(submissionId) {
                this.current_submission = submissionId
                this.isActive = this.submission_validation;
                if (this.isActive === false) {
                    VueEvent.$emit('show-notification', `You can't register a submission with result less than ${this.charon['defense_threshold']}%`, 'danger')
                }
            },

            validateSubmission(submission) {
                var result = submission.results[0].calculated_result;
                var maxResult = this.getGrademapByResult(submission.results[0]).grade_item.grademax;
                this.submission_validation = result / maxResult >= this.charon['defense_threshold'] / 100;
            },

            getCharon() {
                axios.get(`api/charons/${this.charon_id}/all?id=${this.charon_id}`).then(result => {
                    this.charon = result.data;
                })
            },

            getLabs() {
                axios.get(`api/charons/${this.charon_id}/labs?id=${this.charon_id}`).then(result => {
                    this.labs = result.data;
                });
            },

            getDefenseData() {
                axios.get(`api/charons/${this.charon_id}/registrations?id=${this.charon_id}&studentid=${this.student_id}`).then(result => {
                    this.defenseData = result.data;
                })
            },

            closePopUp() {
                this.isActive = false;
                this.isActiveDefenses = false;
            },

            sendData() {
                if (this.value !== null && this.value['start'] !== null && this.value_time !== null && this.selected.length !== 0) {
                    let choosen_time = this.value['start'].split(' ')[0] + " " + this.value_time;
                    let selected_boolean = this.selected === "My teacher";

                    axios.post(`api/charons/${this.charon_id}/submission`, {
                        student_id: this.student_id,
                        charon_id: this.charon_id,
                        submission_id: this.current_submission,
                        selected: selected_boolean,
                        defense_lab_id: this.value['id'],
                        student_chosen_time: choosen_time,
                    }).then(result => {
                        this.getDefenseData();
                        this.editDataAfterInsert(result.data)
                    })
                } else {
                    VueEvent.$emit('show-notification', "Needed parameters weren't inserted!", 'danger')
                }
            },

            editDataAfterInsert(dataFromDb) {
                switch (dataFromDb) {
                    case 'teacher is busy':
                        VueEvent.$emit('show-notification', "Your teacher isn't vacant at given time.\nPlease choose another time or if possible, another teacher.", 'danger')
                        break;
                    case 'user in db':
                        VueEvent.$emit('show-notification', "You cannot register twice for one exercise.\n If you want to choose another time, then you should delete your previous time (My registrations button)", 'danger')
                        break;
                    case 'inserted':
                        VueEvent.$emit('show-notification', "Registration was successful!", 'primary')
                        this.isActive = false
                        break;
                    case 'invalid setup':
                        VueEvent.$emit('show-notification', "Alert teachers that lab configuration was invalid", 'danger')
                        break;
                    case 'invalid chosen time':
                        VueEvent.$emit('show-notification', "Invalid chosen time", 'danger')
                        break;
                }
            },

            getGrademapByResult(result) {
                let correctGrademap = null;
                this.grademaps.forEach(grademap => {
                    if (grademap.grade_type_code === result.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });
                return correctGrademap;
            },

            toggleAdvanced(submissionId) {
                if (this.advanced.includes(submissionId)) {
                    let index = this.advanced.indexOf(submissionId);
                    this.advanced.splice(index, 1);
                } else {
                    this.advanced.push(submissionId);
                }
            },

            showingAdvanced(submissionId) {
                return this.advanced.includes(submissionId);
            },

            submissionString(submission) {
                let size = submission.results.length

                if (size > 3) {
                    return "" +
                        submission.results[0].calculated_result + " | ... | " +
                        submission.results[size - 2].calculated_result + " | " +
                        submission.results[size - 1].calculated_result

                } else {
                    let resultStr = '';
                    let prefix = '';

                    submission.results.forEach((result) => {
                        resultStr += prefix;
                        resultStr += result.calculated_result;
                        prefix = ' | ';
                    });
                    return resultStr;
                }
            },
            refreshSubmissions() {
                this.refreshing = true;
                Submission.findByUserCharon(this.student_id, this.charon_id, (submissions) => {
                    this.submissions = submissions;
                    this.canLoadMore = Submission.canLoadMore();
                    this.refreshing = false;
                });
            },

            showStudentDefenses() {
                this.isActiveDefenses = true;
            },

            loadMoreSubmissions() {
                if (Submission.canLoadMore()) {
                    Submission.getNext(submissions => {
                        submissions.forEach(submission => this.submissions.push(submission));
                        this.canLoadMore = Submission.canLoadMore();
                    });
                } else {
                    this.canLoadMore = false;
                }
            },

        },
        mounted() {
            this.getDefenseData();
            this.refreshSubmissions();
            this.getCharon();
            this.getLabs();

            if (this.charon['choose_teacher'] === 1) {
                this.teacher_options = ["Any teacher"]
            } else {
                this.teacher_options = ["My teacher", "Any teacher"]
            }
        }

    }
</script>

<style>
    @import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
    @import url("https://fonts.googleapis.com/css?family=Material+Icons");
</style>
