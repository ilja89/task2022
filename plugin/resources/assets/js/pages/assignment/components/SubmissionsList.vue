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
                    <h4>Choose a lab session to defend [charon activity name]</h4>
                </div>
                <div class="labs-schedule">
                    <datepicker :datetime="datetime" :placeholder="placeholder"></datepicker>
                    <div class="row">
                    </div>
                    <div class="register-lab-headers">
                        <h4>Choose a teacher</h4>
                    </div>
                    <div class="labs-schedule">
                        <div class="row">
                            <div class="col-6 col-sm-4"><label for="my-teacher"></label><input type="radio" v-model="selected" id="my-teacher" value="My teacher" name="labs-time">My teacher</div>
                            <div class="w-100 d-none d-md-block"></div>
                            <div class="col-6 col-sm-4"><label for="another-teacher"></label><input type="radio" v-model="selected" id="another-teacher" value="Another teacher" name="labs-time">Another teacher</div>
                        </div>
                    </div>
                </div>
                <div class="register-lab-button-dev" @click="sendData()">
                    <button class="button" type="button">Register</button>
                </div>
            </div>

        </Modal>
        <ul class="submissions-list">
            <template v-for="submission in submissions">
                <li class="submission-row" :class="{ active: showingAdvanced(submission.id) }"
                    @click="toggleAdvanced(submission.id)">

                    <span class="tag is-info">
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
                    <span class="kilb-icon" @click.stop="showModalLabs(submission.id)">
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

    </div>
</template>
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
    import { Translate } from '../../../mixins';
    import { Submission } from '../../../api';
    import Modal from '../../../components/partials/Modal.vue';
    import Datepicker from "../../../components/partials/Datepicker.vue";
    import moment from "moment";


    export default {
        mixins: [ Translate ],
        components: {
            Modal, Datepicker
        },

        props: {
            grademaps: { required: true },
            charon_id: { required: true },
            student_id: { required: true },
        },

        data() {
            return {
                advanced: [],
                submissions: [],
                current_submission: 0,
                selected: '',
                selected_boolean: false,
                canLoadMore: true,
                refreshing: false,
                isActive: false,
                datetime: {},
                placeholder: 'Select date',
                project: {}
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
            showModalLabs(submissionId) {
                this.isActive = true;
                this.current_submission = submissionId
            },
            closePopUp() {
                this.isActive = false;
            },
            sendData() {
                this.selected_boolean = this.selected === "My teacher";

                if (Object.keys(this.datetime).length !== 0 && this.selected.length !== 0) {
                    Submission.SendData(this.student_id, this.current_submission, this.datetime, this.selected_boolean)
                } else {
                    alert("You didnt insert needed parameters!")
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
                this.refreshing = true;
                Submission.findByUserCharon(this.student_id, this.charon_id, (submissions) => {
                    this.submissions = submissions;
                    this.canLoadMore = Submission.canLoadMore();
                    this.refreshing = false;
                });
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
            this.refreshSubmissions();
        }
    }
</script>
