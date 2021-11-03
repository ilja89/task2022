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

                <v-card >
                    <div v-if="!toggleShowAllSubmissions">
                        <h3>Feedback for this submission</h3>
                    </div>
                    <div v-else>
                        <h3>Feedback for all submissions for this charon</h3>
                    </div>
                    <label class="switch">
                        <input type="checkbox" v-model="toggleShowAllSubmissions">
                        <span class="slider round"></span>
                    </label>

                    <files-with-review-comments v-if="hasReviewComments"
                                   :filesWithReviewComments="this.getFilesWithReviewComments()" view="teacher"/>
                    <v-card v-else class="message">
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
import {CharonTabs, CharonTab, FilesComponent, ReviewCommentComponent, FilesWithReviewComments} from '../../../components/partials/index';
import {PopupSection} from '../layouts/index';
import {OutputComponent} from '../partials/index';
import {ReviewComment, Submission} from "../../../api";
import {File} from "../../../api";

export default {

    components: {
        PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent, ReviewCommentComponent, FilesWithReviewComments
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
            if(this.filesWithReviewComments) {
                return this.getFilesWithReviewComments().length > 0;
            }
            return false;
        },

        /*hasReviewComments() {
            for (let i = 0; i < this.submission.files.length; i++) {
                if (this.submission.files[i].review_comments.length > 0) {
                    return true;
                }
            }
            return false;
        },*/

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

    mounted: function () {
        this.$root.$on('refresh_submission_files', () => {

            File.findBySubmission(this.submission.id, newFile => {
                this.submission.files = newFile
            })
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

.message {
    padding: 10px;
}
</style>
