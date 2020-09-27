<template>
    <div>

        <page-title :student="student"></page-title>

        <submission-overview-section/>

        <comments-section/>

        <output-section/>

    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from "vuex";

    import {PageTitle} from "../partials";
    import {SubmissionOverviewSection, OutputSection, CommentsSection} from "../sections";
    import {Submission, Charon} from "../../../api";

    export default {
        components: {PageTitle, SubmissionOverviewSection, OutputSection, CommentsSection},

        data() {
            return {
                guard_navigation: false
            };
        },

        computed: {
            ...mapState(["student", "charon"]),

            ...mapGetters(["courseId"])
        },

        mounted() {
            this.getSubmission();

            window.VueEvent.$on("submission-was-saved", _ => {
                this.getSubmission;
                this.guardFromNavigation(false)
            });
            window.VueEvent.$on("submission-being-edited", _ =>
                this.guardFromNavigation(true)
            );
        },

        activated() {
            this.getSubmission();
            window.VueEvent.$on("refresh-page", this.getSubmission);
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off("refresh-page", this.getSubmission);
        },

        beforeRouteLeave(to, from, next) {
            if (this.guard_navigation === true) {
                const answer = window.confirm(
                    "Do you really want to leave? you have unsaved changes!"
                );
                if (answer) {
                    next();
                } else {
                    next(false);
                }
            } else {
                next();
            }
        },

        watch: {
            $route() {
                if (typeof this.$route.params.submission_id !== "undefined") {
                    this.getSubmission();
                }
            }
        },

        methods: {
            ...mapActions(["fetchStudent", "updateCharon", "updateSubmission"]),

            getSubmission() {
                Submission.findById(this.$route.params.submission_id, submission => {
                        this.updateSubmission({submission});
                        const charonId = submission.charon_id;

                        Charon.all(this.courseId, charons => {
                            charons.forEach(charon => {
                                if (charon.id === charonId) {
                                    this.updateCharon({charon});
                                }
                            });
                        });


                        const studentId = submission.user_id;
                        const courseId = this.courseId;

                        this.fetchStudent({studentId, courseId});
                    }
                );
            },
            guardFromNavigation(state) {
                this.guard_navigation = state;
            }
        }
    };
</script>
