<template>

    <popup-section
            :title="activeCharonName"
            subtitle="Grade the students submission">

        <template slot="header-right">
            <button class="button is-primary  save-submission-btn" @click="saveSubmission">
                Save
            </button>
        </template>

        <div class="columns is-gapless  submission-overview-container">

            <div class="column is-one-third card" v-if="hasSubmission">
                <div class="timestamp-info  submission-timestamp">Git time:</div>
                <div class="submission-timestamp">{{ submission.git_timestamp.date.replace(/\:..\.000+/, "") }}</div>

                <div class="submission-deadlines" v-if="hasDeadlines">
                    <div class="timestamp-info">Deadlines:</div>
                    <ul>
                        <li v-for="deadline in deadlines">{{ deadline.deadline_time.date.replace(/\:00.000+/, "") }} - {{ deadline.percentage }}%</li>
                    </ul>
                </div>
            </div>

            <div class="column is-7 card" v-if="hasSubmission">
                <div v-for="(result, index) in submission.results" v-if="getGrademapByResult(result) !== null">

                    <hr v-if="index !== 0" class="hr-result">
                    <div class="result">
                        <div>
                            {{ getGrademapByResult(result).name }}
                            <span class="grademax">/ {{ getGrademapByResult(result).grade_item.grademax }}p</span>
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
    import PopupSection from '../partials/PopupSection.vue';

    export default {
        components: { PopupSection },

        props: {
            context: { required: true },
            submission: { default: null }
        },

        computed: {
            hasSubmission() {
                return this.submission !== null;
            },

            deadlines() {
                return this.context.active_charon.deadlines;
            },

            hasDeadlines() {
                return this.context.active_charon.deadlines.length !== 0;
            },

            activeCharonName() {
                return this.context.active_charon !== null
                    ? this.context.active_charon.name
                    : null;
            }
        },

        methods: {
            getGrademapByResult(result) {
                let correctGrademap = null;
                this.context.active_charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            saveSubmission() {
                VueEvent.$emit('save-active-submission');
            }
        }
    }
</script>
