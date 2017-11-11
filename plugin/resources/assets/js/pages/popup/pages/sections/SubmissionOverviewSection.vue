<template>

    <popup-section
            :title="activeCharonName"
            :subtitle="submissionOrderNrText"
    >

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
                <submission-info
                        :charon="charon"
                        :submission="submission"
                >
                </submission-info>
            </div>

            <div class="column is-7 card">
                <div v-for="(result, index) in submission.results" v-if="getGrademapByResult(result)" :key="result.id">

                    <hr v-if="index !== 0" class="hr-result">
                    <div class="result">
                        <div>
                            {{ getGrademapByResult(result).name }}
                            <span class="grademax">/ {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}p</span>
                        </div>

                        <div class="result-input-container">
                            <input type="number"
                                   step="0.01"
                                   class="input has-text-centered"
                                   :class="{ 'is-danger': resultHasError(result) }"
                                   v-model="result.calculated_result"
                                   @keydown="errors[result.id] = false">

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
    import { PopupSection } from '../../layouts'
    import { Submission, Charon } from '../../../../models'
    import { SubmissionInfo } from '../../components'

    export default {
        components: { PopupSection, SubmissionInfo },

        props: {
            charon: { required: true },
            submission: { default: null }
        },

        data() {
            return {
                charon_confirmed_points: null,
                errors: { },
            };
        },

        computed: {
            hasSubmission() {
                return this.submission !== null;
            },

            activeCharonName() {
                return this.charon !== null
                    ? `<a
                        href="/mode/charon/view.php?id=${this.charon.course_module_id}"
                        class="section-title-link"
                        target="_blank"
                    >
                        ${this.charon.name}
                    </a>`
                    : null;
            },

            submissionOrderNrText() {
                if (! this.submission) {
                    return null
                }

                return this.submission.order_nr + '. submission'
            },
        },

        watch: {
            submission() {
                if (this.submission !== null) {
                    Charon.getResultForStudent(this.charon.id, this.submission.user_id, points => {
                        this.charon_confirmed_points = points
                    })
                }
            },
        },

        filters: {
            datetime(date) {
                return date.replace(/:[0-9]{2}\.[0-9]+/, '');
            },

            withoutTrailingZeroes(number) {
                return parseFloat(number);
            },
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
                    if (response.status !== 200) {
                        VueEvent.$emit('show-notification', response.data.detail, 'danger', 5000)

                        let newErrors = { ...this.errors }
                        newErrors[response.data.resultId] = true

                        this.errors = newErrors
                    } else {
                        this.submission.confirmed = 1
                        VueEvent.$emit('submission-was-saved')
                        VueEvent.$emit('show-notification', response.data.message)
                        VueEvent.$emit('refresh-page')
                    }
                });
            },

            setMaxPoints(result) {
                result.calculated_result = parseFloat(this.getGrademapByResult(result).grade_item.grademax);
            },

            resultHasError(result) {
                return !!this.errors[ result.id ]
            },
        }
    }
</script>

<style>

</style>
