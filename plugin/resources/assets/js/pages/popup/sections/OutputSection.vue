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
                <h3 v-if="toggleOn">Showing table</h3>
                <h3 v-else>Showing mail</h3>

                <label class="switch">
                    <input type="checkbox" v-model="toggleOn">
                    <span class="slider round"></span>
                </label>

                <v-card class="mx-auto" max-height="900" max-width="80vw" outlined raised >
                    <pre v-if="hasMail && !toggleOn" style="max-height: 900px;overflow: auto" v-html="submission.mail"/>

                    <pre v-if="toggleOn" style="max-height: 900px;overflow: auto">
                        <submission-table-component :testSuites="submission['test_suites']"></submission-table-component>
                    </pre>
                </v-card>

            </charon-tab>

            <charon-tab name="Feedback">

                <review-comment-component v-if="hasReviewComments" :files="submission.files" view="teacher"/>

                <v-card v-else class="message">
                    When a teacher adds feedback for the submission, it will be visible here.
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
    import {CharonTabs, CharonTab, FilesComponent, ReviewCommentComponent} from '../../../components/partials/index';
    import {PopupSection} from '../layouts/index';
    import {OutputComponent} from '../partials/index';
    import {Submission} from "../../../api";
    import {File} from "../../../api";
    import SubmissionTableComponent from "../../../components/partials/SubmissionTableComponent"

    export default {

        components: {
            PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent,
          ReviewCommentComponent, SubmissionTableComponent
        },

        data() {
            return {
                stickyTabs: false,
                toggleOn: false
            }
        },

        computed: {
            ...mapState([
                'charon',
                'submission',
            ]),

            hasReviewComments() {
                for (let i = 0; i < this.submission.files.length; i++) {
                    if (this.submission.files[i].review_comments.length > 0) {
                        return true;
                    }
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
                Submission.findById(this.submission.id, this.submission.user_id,  submission => {
                    this.updateSubmission({submission});
                })
            }
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
        }
    }
</script>

<style scoped>
@import '../../../../../../public/css/buttons/toggleButton.css';

.mx-auto {
    margin-top: 10px;
}

</style>
