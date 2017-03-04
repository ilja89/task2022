<template>

    <popup-section
            :title="activeCharonName"
            subtitle="Grade the students submission">

        <template slot="header-right">
            <button class="button is-primary  save-submission-btn" @click="saveSubmission">
                Save
            </button>
        </template>

        <div class="columns is-gapless  submission-overview-container" v-if="hasSubmission">

            <div class="column is-one-third card">
                <div class="timestamp-info  submission-timestamp">Git time:</div>
                <div class="submission-timestamp">{{ submission.git_timestamp.date | datetime }}</div>

                <div class="submission-deadlines" v-if="hasDeadlines">
                    <div class="timestamp-info">Deadlines:</div>
                    <ul>
                        <li v-for="deadline in charon.deadlines">{{ deadline.deadline_time.date | datetime }} - {{ deadline.percentage }}%</li>
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

                        <div>
                            <input type="number" step="0.01" v-model="result.calculated_result"
                                   class="has-text-centered">
                        </div>
                    </div>

                </div>

                <div class="submission-confirmed"
                     v-if="submission.confirmed == 1">
                    <hr>
                    <strong>Confirmed</strong>
                </div>
            </div>
        </div>

    </popup-section>

</template>

<script>
    import PopupSection from '../../layouts/PopupSection.vue';
    import Submission from '../../../models/Submission';

    export default {
        components: { PopupSection },

        props: {
            charon: { required: true },
            submission: { default: null }
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

        filters: {
            datetime(date) {
                return date.replace(/\:00.000+/, '');
            },

            withoutTrailingZeroes(number) {
                return number.replace(/000$/, '');
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
                    }
                });
            }
        }
    }
</script>
