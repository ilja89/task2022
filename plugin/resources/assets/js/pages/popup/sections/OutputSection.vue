<template>
    <popup-section
        title="Email and outputs"
        subtitle="Output from the tester and mail sent to the student."
        class="output-section"
    >
        <charon-tabs
            v-if="submission"
            class="card popup-tabs"
            id="tabs"
            :sticky="stickyTabs"
        >
            <charon-tab name="Code" :selected="true">
                <files-component
                    :submission="submission"
                    :testerType="charon ? charon.tester_type_name : ''"
                    :isRound="false"
                />
            </charon-tab>

            <charon-tab name="Mail">
                <v-card class="mx-auto" max-height="900" max-width="80vw" outlined raised v-if="hasMail">
                    <pre style="max-height: 900px;overflow: auto" v-html="submission.mail"/>
                </v-card>
            </charon-tab>

            <charon-tab name="Feedback">
                <v-card class="main-container">
                    <div v-if="!toggleShowAllSubmissions">
                        <h2>Feedback for this submission</h2>
                    </div>
                    <div v-else>
                        <h2>Feedback for all submissions for this charon</h2>
                    </div>
                    <label class="switch">
                        <input type="checkbox" v-model="toggleShowAllSubmissions">
                        <span class="slider round"></span>
                    </label>

                    <files-with-review-comments v-if="hasReviewComments"
                                                :filesWithReviewComments="this.getFilesWithReviewComments()"
                                                view="teacher"/>
                    <v-card v-else class="no-submission-message">
                        When a teacher adds feedback for the submission, it will be visible here.
                    </v-card>
                </v-card>
            </charon-tab>

            <charon-tab name="Outputs">
                <output-component :submission="submission"/>
            </charon-tab>
        </charon-tabs>
    </popup-section>
</template>

<script>

import {mapState, mapActions} from "vuex";
import {CharonTabs, CharonTab, FilesComponent, FilesWithReviewComments} from '../../../components/partials/index';
import {PopupSection} from '../layouts/index';
import {OutputComponent} from '../partials/index';
import {ReviewComment, Submission} from "../../../api";

export default {
    components: {
        PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent, FilesWithReviewComments
    },

    data() {
        return {
            stickyTabs: false,
            toggleShowAllSubmissions: false,
        }
    },

    computed: {
        ...mapState([
            'charon',
            'submission',
            'filesWithReviewComments',
        ]),

        hasReviewComments() {
            if (this.filesWithReviewComments) {
                return this.getFilesWithReviewComments().length > 0;
            }
            return false;
        },

        hasMail() {
            return typeof this.submission.mail !== 'undefined' && this.submission.mail !== null && this.submission.mail.length > 0;
        },
    },

    methods: {
        ...mapActions(["updateSubmission"]),

        updateOutputSection() {
            Submission.findById(this.submission.id, this.submission.user_id, submission => {
                this.updateSubmission({submission});
            })
        },

        getFilesWithReviewComments() {
            if (this.toggleShowAllSubmissions) {
                return this.filesWithReviewComments;
            }
            let $reviewComments = [];
            this.filesWithReviewComments.forEach(reviewComment => {
                if (reviewComment.submissionId === this.submission.id) {
                    $reviewComments.push(reviewComment);
                }
            })
            return $reviewComments;
        },

        getFilesWithCommentsForAllSubmissions($charonId, $studentId) {
            ReviewComment.getReviewCommentsForCharonAndUser($charonId, $studentId, data => {
                this.$store.state.filesWithReviewComments = data;
            })
        },
    },

    created() {
        VueEvent.$on('update-from-review-comment', this.updateOutputSection)
    },

    mounted() {
        this.$root.$on('refresh-review-comments', () => {
            this.getFilesWithCommentsForAllSubmissions(this.submission.charon_id, this.submission.user_id);
        })
    },

    watch: {
        submission() {
            this.getFilesWithCommentsForAllSubmissions(this.submission.charon_id, this.submission.user_id)
        }
    }
}
</script>

<style scoped>

/* The switch - the box around the slider */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    margin-left: 1em;
}

/* Hide default HTML checkbox */
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

/* The slider */
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
}

input:checked + .slider {
    background-color: #2196F3;
}

input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.no-submission-message {
    background-color: #f2f3f4 !important;
    margin: 1em 0.5em 0.5em;
    padding: 0.5em;
    font-size: 1.2em;
}

h2 {
    padding: 0.5em;
    font-size: 1.5em;
}

</style>
