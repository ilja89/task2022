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

                <div v-for="user in studentResults" :key="user.id">

                    <header v-if="isGroupSubmission" class="v-sheet theme--light v-toolbar v-toolbar--flat" style="height: 64px;">
                        <div class="card v-toolbar__content" style="height: 64px;">
                            <div class="v-toolbar__title" style="margin-left: auto; margin-right: auto">{{ user.firstname }} {{ user.lastname }}</div>
                        </div>
                    </header>

                    <div class="card">

                        <div v-for="(result, index) in user.results"
                             v-if="getGrademapByResult(result)" :key="'result_' + index">

                            <hr v-if="index !== 0" class="hr-result"/>

                            <div class="result">
                                <div>
                                  <span v-if="getGrademapByResult(result).persistent > 0" title="This grade is persistent.">* </span>{{ getGrademapByResult(result).name }}
                                    <span class="grademax">
                                        / {{ getGrademapByResult(result).grade_item.grademax | withoutTrailingZeroes }}p
                                    </span>
                                </div>
                                <div class="result-input-container">
                                    <input
                                            class="input has-text-centered"
                                            :class="{ 'is-danger': resultHasError(result) | result.calculated_result > getGrademapByResult(result).grade_item.grademax }"
                                            type="number"
                                            step="0.01"
                                            max="getGrademapByResult(result).grade_item.grademax"
                                            v-model="result.calculated_result"
                                            @change="updatePointsState"
                                            @keydown="errors[result.id] = false"/>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="setMaxPoints(result)">Max
                                    </v-btn>
                                    <div class="resultpercent">
                                        {{ getResultPercent(result) | withoutTrailingZeroes }}%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="hr-result"/>

                        <div class="result">
                            <div>
                                Total {{ user.total_result | withoutTrailingZeroes }}
                                <span class="grademax">/ {{ submission.max_result | withoutTrailingZeroes }}p</span>
                            </div>
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

        watch: {
            submission() {
                this.getTotalResult();
            },
        },

        computed: {
            ...mapState(["charon", "submission", "teacher", ""]),

            ...mapGetters(["charonLink"]),

            isSessionActive() {
                return this.teacher != null
            },

            hasSubmission() {
                return this.submission != null;
            },

            isGroupSubmission() {
                return this.submission.users.length > 1;
            },

            studentResults() {
                let users = this.submission.users;

                users.forEach(user => {
                    user.total_result = this.submission.total_results[user.id];
                    user.results = this.submission.results
                        .filter(result => result.user_id === user.id)
                        .sort((a, b) => a.grade_type_code - b.grade_type_code);
                });

                return users;
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
                if (this.submission) {
                    return "Charon: <b>" + this.submission.charon_order_nr + "</b>. submission" +
                        " — Course: <b>" + this.submission.course_order_nr + "</b>. submission"
                }
                return "no submission present";
            },

            saveIsDisabled() {
                return !this.points_changed;
            },
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

            getResultKey(result) {
                let key = 'result_' + result.user_id + '_' + result.grade_type_code + '_' + result.id;
                return key;
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

            getResultPercent(result) {
                const fixed = parseFloat(result.percentage) * 100;
                return fixed.toFixed(2);
            },

            resultHasError(result) {
                return !!this.errors[result.id];
            },

            getTotalResult() {
                this.charon_confirmed_points = null;
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
