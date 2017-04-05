<template>

    <popup-section
            :title="activeCharonName"
            subtitle="Grade the students submission">

        <template slot="header-right">
            <span class="extra-info-text" v-if="charon_confirmed_points !== null">
                Current points: {{ charon_confirmed_points }}p
            </span>

            <button class="button is-primary  save-submission-btn" @click="saveSubmission">
                Save
            </button>
        </template>

        <div class="columns is-gapless is-desktop  submission-overview-container" v-if="hasSubmission">

            <div class="column is-one-third card">
                <div class="submission-info-title">Git time:</div>
                <div class="submission-info-content">{{ submission.git_timestamp.date | datetime }}</div>

                <div v-if="submission.git_hash !== null && submission.git_hash !== ''">
                    <div class="submission-info-title">Commit hash:</div>
                    <div class="submission-info-content">{{ submission.git_hash }}</div>
                </div>

                <div v-if="submission.git_commit_message !== null && submission.git_commit_message !== ''">
                    <div class="submission-info-title">Commit message:</div>
                    <div class="submission-info-content">{{ submission.git_commit_message }}</div>
                </div>

                <div class="submission-deadlines" v-if="hasDeadlines">
                    <div class="submission-info-title">Deadlines:</div>
                    <ul>
                        <li class="submission-info-content" v-for="deadline in charon.deadlines">{{ deadline.deadline_time.date | datetime }} - {{ deadline.percentage }}%</li>
                    </ul>
                </div>
            </div>

            <div class="column is-7 card">
                <div v-for="(result, index) in submission.results" v-if="getGrademapByResult(result)">

                    <hr v-if="index !== 0" class="hr-result">
                    <div class="result">
                        <div>
                            {{ getGrademapByResult(result).name }}
                            <span class="grademax">/ {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}p</span>
                        </div>

                        <div class="result-input-container">
                            <input type="number" step="0.01" v-model="result.calculated_result"
                                   class="has-text-centered">

                            <a class="button is-primary" @click="setMaxPoints(result)">
                                Max
                            </a>
                        </div>
                    </div>

                </div>

                <hr class="hr-result">
                <div class="result">
                    <div>
                        Total {{ parseFloat(submission.total_result) }}
                        <span class="grademax">/ {{ parseFloat(submission.max_result) }}p</span>
                    </div>
                </div>

                <div class="submission-confirmed"
                     v-if="submission.confirmed == 1">
                    <hr>
                    <div class="confirmed-message">
                        <strong>Confirmed</strong>
                    </div>
                </div>
            </div>
        </div>

    </popup-section>

</template>

<script>
    import { PopupSection } from '../../layouts';
    import { Submission, Charon } from '../../../../models';

    export default {
        components: { PopupSection },

        props: {
            charon: { required: true },
            submission: { default: null }
        },

        data() {
            return {
                charon_confirmed_points: null,
            };
        },

        computed: {
            hasSubmission() {
                return this.submission !== null;
            },

            hasDeadlines() {
                return this.charon.deadlines.length !== 0;
            },

            activeCharonName() {
                return this.charon !== null
                    ? this.charon.name
                    : null;
            }
        },

        watch: {
            submission() {
                if (this.submission !== null) {
                    Charon.getResultForStudent(this.charon.id, this.submission.user_id, points => {
                        this.charon_confirmed_points = points;
                    });
                }
            }
        },

        filters: {
            datetime(date) {
                return date.replace(/:[0-9]{2}\.[0-9]+/, '');
            },

            withoutTrailingZeroes(number) {
                return parseFloat(number);
            }
        },

        methods: {
            getGrademapByResult(result) {
                let correctGrademap = null;
                this.charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            saveSubmission() {
                Submission.update(this.charon.id, this.submission, response => {
                    if (response.status == "OK") {
                        this.submission.confirmed = 1;
                        VueEvent.$emit('submission-was-saved');
                        VueEvent.$emit('show-notification', 'Submission saved!');
                        VueEvent.$emit('refresh-page');
                    }
                });
            },

            setMaxPoints(result) {
                result.calculated_result = parseFloat(this.getGrademapByResult(result).grade_item.grademax);
            }
        }
    }
</script>
