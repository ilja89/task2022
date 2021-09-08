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

            <charon-tab name="Comments">

                <comment-component v-if="hasComments" :submission="submission" view="teacher"/>
                <no-comments-component-popup v-else></no-comments-component-popup>

            </charon-tab>

            <charon-tab name="Outputs">

                <output-component :submission="submission"/>

            </charon-tab>

        </charon-tabs>

    </popup-section>
</template>

<script>

    import {mapState, mapActions} from "vuex";
    import {CharonTabs, CharonTab, FilesComponent, CommentComponent, NoCommentsComponentPopup} from '../../../components/partials/index';
    import {PopupSection} from '../layouts/index';
    import {OutputComponent} from '../partials/index';
    import {Submission} from "../../../api";
    import {File} from "../../../api";

    export default {

        components: {
            NoCommentsComponentPopup,
            PopupSection, CharonTabs, CharonTab, FilesComponent, OutputComponent, CommentComponent},

        data() {
            return {
                stickyTabs: false,
                comments: false
            }
        },

        computed: {
            ...mapState([
                'charon',
                'submission',
            ]),

            hasComments() {
                let comments = false;
                this.submission.files.forEach(file => {
                    if(file.comments.length > 0) {
                        comments = true;
                    }
                });
                return comments;
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
            },
        },

        created() {
            VueEvent.$on('update-from-file-comment', this.updateOutputSection)
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
