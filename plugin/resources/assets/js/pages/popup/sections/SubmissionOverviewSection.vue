<template>
    <popup-section
        :title="activeCharonName"
        :subtitle="submissionOrderNrText"
    >

        <template slot="header-right">
            <span v-if="charon_confirmed_points !== null" class="extra-info-text">
                Current points: {{ charon_confirmed_points }}p
            </span>

            <button class="button is-primary  save-submission-btn" @click="saveSubmission">
                Save
            </button>
        </template>

        <div
            v-if="hasSubmission"
            class="columns  is-gapless  is-desktop  submission-overview-container"
        >

            <div class="column  is-one-third">
                <div class="card">
                    <submission-info/>
                </div>
            </div>

            <div class="column is-7">
                <div class="card">
                    <div
                        v-for="(result, index) in submission.results"
                        v-if="getGrademapByResult(result)"
                        :key="result.id"
                    >

                        <hr v-if="index !== 0" class="hr-result">
                        <div class="result">
                            <div>
                                {{ getGrademapByResult(result).name }}
                                <span class="grademax">/ {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}p</span>
                            </div>

                            <div class="result-input-container">
                                <input
                                    class="input has-text-centered"
                                    :class="{ 'is-danger': resultHasError(result) }"
                                    type="number"
                                    step="0.01"
                                    v-model="result.calculated_result"
                                    @keydown="errors[result.id] = false"
                                >

                                <a class="button is-primary" @click="setMaxPoints(result)">
                                    Max
                                </a>
                            </div>
                        </div>

                    </div>

                    <hr class="hr-result">
                    <div class="result">
                        <div>
                            Total {{ submission.total_result | withoutTrailingZeroes }}
                            <span class="grademax">/ {{ submission.max_result | withoutTrailingZeroes }}p</span>
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
        </div>

    </popup-section>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import { PopupSection } from '../layouts/index'
    import { Submission, Charon } from '../../../api/index'
    import { SubmissionInfo } from '../partials/index'

    export default {
        components: { PopupSection, SubmissionInfo },

        data() {
            return {
                charon_confirmed_points: null,
                errors: {},
            }
        },

        computed: {
            ...mapState([
                'charon',
                'submission',
            ]),

            ...mapGetters([
                'charonLink',
            ]),

            hasSubmission() {
                return this.submission !== null
            },

            activeCharonName() {
                return this.charon !== null
                    ? `<a
                        href="${this.charonLink}"
                        class="section-title-link"
                        target="_blank"
                    >
                        ${this.charon.name}
                    </a>`
                    : null
            },

            submissionOrderNrText() {
                return this.submission
                    ? this.submission.order_nr + '. submission'
                    : null
            },
        },

        watch: {
            submission() {
                this.getTotalResult()
            },

            charon() {
                this.getTotalResult()
            },
        },

        filters: {
            withoutTrailingZeroes(number) {
                return parseFloat(number)
            },
        },

        methods: {
            getGrademapByResult(result) {
                if (!this.charon) return null

                let correctGrademap = null
                this.charon.grademaps.forEach((grademap) => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap
                    }
                })

                return correctGrademap
            },

            saveSubmission() {

                Submission.update(this.charon.id, this.submission, response => {
                    if (response.status !== 200) {
                        window.VueEvent.$emit('show-notification', response.data.detail, 'danger', 5000)

                        let newErrors = { ...this.errors }
                        newErrors[response.data.resultId] = true

                        this.errors = newErrors
                    } else {
                        this.submission.confirmed = 1
                        window.VueEvent.$emit('submission-was-saved')
                        window.VueEvent.$emit('show-notification', response.data.message)
                        window.VueEvent.$emit('refresh-page')

                        this.errors = {}

                        this.getTotalResult()
                    }
                })
            },

            setMaxPoints(result) {
                result.calculated_result = parseFloat(
                    this.getGrademapByResult(result).grade_item.grademax,
                )
            },

            resultHasError(result) {
                return !!this.errors[result.id]
            },

            getTotalResult() {
                if (this.submission === null || this.charon === null) return

                Charon.getResultForStudent(this.charon.id, this.submission.user_id, points => {
                    this.charon_confirmed_points = points
                })
            },
        },
    }
</script>
