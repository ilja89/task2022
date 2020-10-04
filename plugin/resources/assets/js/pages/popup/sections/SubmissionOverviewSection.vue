<template>
    <popup-section :title="activeCharonName" :subtitle="submissionOrderNrText">
        <template slot="header-right">
            <span v-if="charon_confirmed_points !== null" class="extra-info-text">
                Current points: {{ charon_confirmed_points }}p
            </span>
            <v-btn :disabled="saveIsDisabled" class="ma-2" tile outlined color="primary" @click="saveSubmission">
                Save
            </v-btn>
            <v-btn v-if="isSessionActive" :disabled="goBackIsDisabled" class="ma-2" tile outlined color="primary"
                   @click="goBack()">
                Go Back
            </v-btn>
        </template>

        <div v-if="hasSubmission" class="columns is-gapless is-desktop submission-overview-container">
            <div class="column is-one-third">
                <div class="card">
                    <submission-info/>
                </div>
            </div>

            <div class="column is-7">
                <div class="card">
                    <div v-for="(result, index) in submission.results"
                         v-if="getGrademapByResult(result)"
                         :key="'result_' + index + '_' + submission.id">

                        <hr v-if="index !== 0" class="hr-result"/>

                        <div class="result">
                            <div>
                                {{ getGrademapByResult(result).name }}
                                <span
                                        class="grademax"
                                >/ {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}p</span>
                            </div>

                            <div class="result-input-container">
                                <input
                                        class="input has-text-centered"
                                        :class="{ 'is-danger': resultHasError(result) | result.calculated_result > getGrademapByResult(result).grade_item.grademax }"
                                        type="number"
                                        step="0.01"
                                        max="getGrademapByResult(result).grade_item.grademax"
                                        v-model="result.calculated_result"
                                        @keyup="updatePointsState"
                                        @keydown="errors[result.id] = false"
                                />
                                <v-btn class="ma-2" tile outlined color="primary" @click="setMaxPoints(result)">Max
                                </v-btn>
                            </div>
                        </div>
                    </div>

                    <hr class="hr-result"/>
                    <div class="result">
                        <div>
                            Total {{ submission.total_result | withoutTrailingZeroes }}
                            <span
                                    class="grademax"
                            >/ {{ submission.max_result | withoutTrailingZeroes }}p</span>
                        </div>
                    </div>

                    <div class="submission-confirmed" v-if="submission.confirmed == 1">
                        <hr/>
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
    import {mapState, mapGetters} from "vuex";
    import {PopupSection} from "../layouts/index";
    import {Submission, Charon} from "../../../api/index";
    import {SubmissionInfo} from "../partials/index";

    export default {
        components: {PopupSection, SubmissionInfo},

        data() {
            return {
                charon_confirmed_points: null,
                errors: {},
                points_changed: false,
                goBackIsDisabled: true
            };
        },

        computed: {
            ...mapState(["charon", "submission", "teacher"]),

            ...mapGetters(["charonLink"]),

            isSessionActive() {
                return this.teacher != null
            },

            hasSubmission() {
                return this.submission != null;
            },

            activeCharonName() {
                return this.charon != null
                    ? `<a
                        href="${this.charonLink}"
                        class="section-title-link"
                        target="_blank"
                    >
                        ${this.charon.name}
                    </a>`
                    : null;
            },

            submissionOrderNrText() {
                return this.submission ? this.submission.order_nr + ". submission" : null;
            },

            saveIsDisabled() {
                return !this.points_changed;
            },

            goBackIsDisabled() {
                return !this.points_changed;
            }
        },

        filters: {
            withoutTrailingZeroes(number) {
                return parseFloat(number);
            }
        },


        methods: {
            getGrademapByResult(result) {
                if (!this.charon) return null;

                let correctGrademap = null;
                this.charon.grademaps.forEach(grademap => {
                    if (result.grade_type_code == grademap.grade_type_code) {
                        correctGrademap = grademap;
                    }
                });

                return correctGrademap;
            },

            goBack() {
                this.goBackIsDisabled = true;
                this.$router.back();
            },

            saveSubmission() {
                for (let res in this.submission.results) {
                    if (this.submission.results[res].calculated_result >
                        parseFloat(this.getGrademapByResult(this.submission.results[res]).grade_item.grademax)
                    ) {
                        window.VueEvent.$emit(
                            "show-notification",
                            this.getGrademapByResult(this.submission.results[res]).name +
                            " points are out of range",
                            "danger",
                            5000
                        );
                        return;
                    }
                }

                const responseHandler = response => {
                    if (response.status !== 200) {
                        window.VueEvent.$emit(
                            "show-notification",
                            response.data.detail,
                            "danger",
                            5000
                        );

                        let newErrors = {...this.errors};
                        newErrors[response.data.resultId] = true;

                        this.errors = newErrors;
                    } else {
                        this.points_changed = false;
                        this.submission.confirmed = 1;
                        window.VueEvent.$emit("submission-was-saved");
                        window.VueEvent.$emit("show-notification", response.data.message);
                        window.VueEvent.$emit("refresh-page");

                        this.errors = {};

                        this.getTotalResult();
                    }
                }

                Submission.update(this.charon.id, this.submission, responseHandler);
                this.goBackIsDisabled = false;

            },

            setMaxPoints(result) {
                // TODO: compare to the current grade?
                this.points_changed = true;
                result.calculated_result = parseFloat(
                    this.getGrademapByResult(result).grade_item.grademax
                );
            },

            resultHasError(result) {
                return !!this.errors[result.id];
            },

            getTotalResult() {
                if (this.submission == null || this.charon == null) return;

                Charon.getResultForStudent(
                    this.charon.id,
                    this.submission.user_id,
                    points => {
                        this.charon_confirmed_points = points;
                    }
                );
            },
            updatePointsState() {
                if (this.points_changed !== true) {
                    this.points_changed = true;
                    window.VueEvent.$emit("submission-being-edited", {});
                }
            }
        }
    };
</script>
