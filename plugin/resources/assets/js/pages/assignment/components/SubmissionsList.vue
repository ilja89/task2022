<template>
    <div>
        <h2 class="title">{{ translate('submissionsText') }}
            <svg @click="refreshSubmissions()" class="svg-icon" v-bind:class="this.refreshing? 'svg-icon rotating' : 'svg-icon'" viewBox="0 0 20 20">
                <path fill="none" d="M3.254,6.572c0.008,0.072,0.048,0.123,0.082,0.187c0.036,0.07,0.06,0.137,0.12,0.187C3.47,6.957,3.47,6.978,3.484,6.988c0.048,0.034,0.108,0.018,0.162,0.035c0.057,0.019,0.1,0.066,0.164,0.066c0.004,0,0.01,0,0.015,0l2.934-0.074c0.317-0.007,0.568-0.271,0.56-0.589C7.311,6.113,7.055,5.865,6.744,5.865c-0.005,0-0.01,0-0.015,0L5.074,5.907c2.146-2.118,5.604-2.634,7.971-1.007c2.775,1.912,3.48,5.726,1.57,8.501c-1.912,2.781-5.729,3.486-8.507,1.572c-0.259-0.18-0.618-0.119-0.799,0.146c-0.18,0.262-0.114,0.621,0.148,0.801c1.254,0.863,2.687,1.279,4.106,1.279c2.313,0,4.591-1.1,6.001-3.146c2.268-3.297,1.432-7.829-1.867-10.101c-2.781-1.913-6.816-1.36-9.351,1.058L4.309,3.567C4.303,3.252,4.036,3.069,3.72,3.007C3.402,3.015,3.151,3.279,3.16,3.597l0.075,2.932C3.234,6.547,3.251,6.556,3.254,6.572z"></path>
            </svg>
        </h2>
        <Modal v-bind:isActive="isActive" @modal-was-closed="closePopUp">
            <template slot="header">
                <p class="modal-card-title">Register for defence</p>
            </template>
            <div class="content">
                <div class="register-lab-headers">
                    <h4>Choose a lab session to defend {{this.charon['name']}}</h4>
                </div>
                <div class="labs-schedule">
                    <div class="text-center">
                        <multiselect v-model="value" :options="this.labs" :block-keys="['Tab', 'Enter']" @select="onSelect" :max-height="200"
                                     :custom-label="getLabList" placeholder="Select day of practise" label="start" track-by="start" :allow-empty="false">
                            <template slot="singleLabel" slot-scope="{ option }">{{ option.start }}</template>
                        </multiselect>

                        <multiselect style="margin-top: 30px" v-model="value_time" :class="{secondMultiselect: secondMultiselect}" :max-height="200"
                                     :options="this.time" placeholder="Select suitable time for you">
                        </multiselect>
                    </div>
                    <div class="register-lab-headers" style="margin-top: 6vh">
                        <h4>Choose a teacher</h4>
                    </div>
                    <div class="labs-schedule">
                        <div class="row">
                            <div class="col-6 col-sm-4" v-if="this.student_group !== 0"><label for="my-teacher"></label><input type="radio" v-model="selected" id="my-teacher" value="My teacher" name="labs-time">My teacher</div>
                            <div class="w-100 d-none d-md-block"></div>
                            <div class="col-6 col-sm-4" v-if="(this.charon['choose_teacher'] === 1 || this.student_group === 0)">
                                <label for="another-teacher"></label>
                                <input type="radio" v-model="selected" id="another-teacher" value="Another teacher" name="labs-time">Another teacher</div>
                        </div>
                    </div>
                </div>
                <div class="register-lab-button-dev" @click="sendData()">
                    <button class="button" type="button">Register</button>
                </div>
            </div>
        </Modal>

        <Modal v-bind:is-active="isActiveDefenses" @modal-was-closed="closePopUp">
            <template slot="header">
                <p class="modal-card-title">All defences</p>
            </template>
            <StudentDefenses :defenseData="defenseData" :student_id="student_id" :charon="charon"></StudentDefenses>
        </Modal>

        <ul class="submissions-list">
            <template v-for="(submission, index) in submissions">
                <li class="submission-row" :class="{ active: showingAdvanced(submission.id) }"
                    @click="toggleAdvanced(submission.id)" :key="index">

                    <span class="tag is-info" :class="{registered: listStyle(submission.id)}">
                        {{ submissionString(submission) }}
                    </span>

                    <span class="submission-time">
                        {{ submission.git_timestamp.date | date }}
                    </span>

                    <span class="dropdown-arrow">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path
                                d="M0 0h24v24H0z" fill="none"/></svg>
                    </span>

                    <span class="open-modal-btn" @click.stop="$emit('submission-was-activated', submission)">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </span>
                    <span class="kilb-icon" @click="ValidationForDefReg(submission)" @click.stop="showModalLabs(submission.id)">
                        <img src="shield.png" alt="kilb">
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
                Load more
            </button>
        </div>
        <div class="has-text-centered">
            <span>
            <button class="button is-primary load-more-button" @click.stop="showStudentDefenses()">
                My defenses
            </button>
            </span>
        </div>

    </div>
</template>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped>
    /* -----
SVG Icons - svgicons.sparkk.fr
----- */

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

.test {
    margin-bottom: 0.5vw;
}

.text-center {
    text-align:center;
}

.rotating
    {
  animation-name: spin;
  animation-duration: 1000ms;
  animation-iteration-count: infinite;
  animation-timing-function: linear;
    }

    .rotating
    {
        animation-name: spin;
        animation-duration: 1000ms;
        animation-iteration-count: infinite;
        animation-timing-function: linear;
    }
    @keyframes spin {
        from {
            transform:rotate(0deg);
        }
        to {
            transform:rotate(-360deg);
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
    import Lab from "../../../api/Lab";

    let url_string = window.location.href;
    let url = new URL(url_string);
    let id = url.searchParams.get("id");

    export default {

        mixins: [ Translate ],
        components: {
            Modal, Datepicker, Multiselect, StudentDefenses
        },

        props: {
            grademaps: { required: true },
            charon_id: { required: true },
            student_id: { required: true },
        },

        data() {
            return {
                my_teacher: true,
                value: null,
                value_time: null,
                secondMultiselect: true,
                advanced: [],
                submissions: [],
                index: 0,
                registered: false,
                current_submission: 0,
                selected: '',
                selected_lab: Object,
                selected_boolean: false,
                canLoadMore: true,
                refreshing: false,
                isActive: false,
                isActiveDefenses: false,
                charon: '',
                time: [],
                labs: [],
                notavailable_time: [],
                defenseData: [],
                submission_validation: false,
                student_group: 0,
            };
        },

        filters: {
            withoutTrailingZeroes(number) {
                return number.replace(/000$/, '');
            },

            date(date) {
                return window.moment(date, "YYYY-MM-DD HH:mm:ss").format("DD/MM HH:mm");
            }
        },

        methods: {
            arrayDefenseTime(option) {
                this.time.length = 0;
                let defense_duration = this.charon['defense_duration'];
                let startTime = moment(option['start'].split(" ")[1], 'HH:mm:ii');
                let endTime = moment(option['end'].split(" ")[1], 'HH:mm:ii');
                let start = option['start'];
                let end = option['end'];

                let time = option['start'].split(' ')[0];
                axios.get(`api/get_time.php?time=${time}&course=${this.charon['course']}&start=${start}&end=${end}&lab_id=${option['id']}&charon_id=${this.charon_id}`).then(result => {
                    this.notavailable_time = result.data;
                }).then(() => {
                    while (startTime < endTime) {
                        this.time.push(new moment(startTime).format('HH:mm'));
                        startTime.add(defense_duration, 'minutes');
                    }
                    if (this.notavailable_time.length !== 0) {
                        this.time = this.time.filter(x => !this.notavailable_time.includes(x));
                    }
                })
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

            onSelect(option) {
                if (option != null) {
                    this.arrayDefenseTime(option);
                    this.secondMultiselect = false;
                    this.modalSize = true;
                }
            },

            showModalLabs(submissionId) {
                this.current_submission = submissionId
                this.isActive = this.submission_validation;
                if (this.isActive === false) {
                    alert("You can't registrate submission with result less then 50%")
                }
            },
            ValidationForDefReg(submission) {
                var result = submission.results[0].calculated_result;
                var maxResult = this.getGrademapByResult(submission.results[0]).grade_item.grademax;
                this.submission_validation = result / maxResult >= 0.5;
            },
            getCharonAndLabs() {
                axios.get(`api/charon_data.php?id=${this.charon_id}`).then(result => {
                    this.charon = result.data;
                })
                axios.get(`api/labs_by_charon.php?id=${this.charon_id}`).then(result => {
                    this.labs = result.data;
                });
            },

            getStudentGroup() {
                axios.get(`api/student_group.php?studentid=${this.student_id}`).then(result => this.student_group = result.data);
            },

            getDefenseData() {
                axios.get(`api/student_defense_data.php?id=${this.charon_id}&studentid=${this.student_id}&group=${this.student_group}`).then(result => {this.defenseData = result.data;})
            },

            closePopUp() {
                this.isActive = false;
                this.isActiveDefenses = false;
            },

            sendData() {
                let selected_boolean = this.selected === "My teacher";
                let datetime_start = this.value['start'];
                let datetime_end = this.value['end'];
                let choosen_time = datetime_start.split(' ')[0] + " " + this.value_time;

                if (this.value !== 0 && this.value_time.length !== 0 && this.selected.length !== 0) {
                    axios.post(`view.php?id=${id}&studentid=${this.student_id}`, {
                        charon_id: this.charon_id,
                        course_id: this.charon['course'],
                        submission_id: this.current_submission,
                        lab_start: datetime_start,
                        lab_end: datetime_end,
                        selected: selected_boolean,
                        defense_lab_id: this.value['id'],
                        student_choosen_time: choosen_time,
                    }).then(result => {
                        this.getDefenseData();
                        this.editDataAfterInsert(result.data)
                    })
                } else alert("You didnt insert needed parameters!")
            },

            editDataAfterInsert(dataFromDb) {
                switch (dataFromDb) {
                    case 'teacher is busy':
                        alert("Your teacher is busy for this time.\nPlease choose another time or if it possible another teacher.");
                        break;
                    case 'user in db':
                        alert('You cannot register twice for one practise.\n If tou want to choose another time, then you shoul delete your previous time (My defenses button)');
                        break;
                    case 'inserted':
                        alert('You was successfully registered for defense!');
                        break;
                    case 'deleted':
                        alert('This time is busy. Please chose another one or choose option "Another teacher"');
                        break;
                }
            },

            getGrademapByResult(result) {
                let correctGrademap = null;
                this.grademaps.forEach(grademap => {
                    if (grademap.grade_type_code == result.grade_type_code) {
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
                let resultStr = '';
                let prefix = '';

                submission.results.forEach((result) => {
                    resultStr += prefix;
                    resultStr += result.calculated_result;
                    prefix = ' | ';
                });
                return resultStr;
            },
            refreshSubmissions() {
                console.log(this.charon_id)
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
            this.getStudentGroup();
            this.getDefenseData();
            this.refreshSubmissions();
            this.getCharonAndLabs();
        }

    }
</script>
