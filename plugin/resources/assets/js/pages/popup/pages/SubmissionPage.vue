<template>
    <div>
        <page-title :student="student"></page-title>

        <submission-overview-section/>

        <output-section/>

        <comments-section/>
    </div>
</template>

<script>
    import {mapState, mapActions, mapGetters} from "vuex";

    import {PageTitle} from "../partials";
    import {SubmissionOverviewSection, OutputSection, CommentsSection} from "../sections";
    import {Submission} from "../../../api";

    export default {
        components: {PageTitle, SubmissionOverviewSection, OutputSection, CommentsSection},

        data() {
            return {
                guard_navigation: false
            };
        },

        computed: {
            ...mapState(["student", "charon", "charons"]),

            ...mapGetters(["courseId"])
        },

        watch: {
            $route() {
                if (typeof this.$route.params.submission_id !== "undefined") {
                    this.getSubmission();
                }
            }
        },

        created() {
            this.getSubmission();

            window.VueEvent.$on("submission-was-saved", _ => {
                this.getSubmission();
                this.guardFromNavigation(false)
            });
            window.VueEvent.$on("submission-being-edited", _ =>
                this.guardFromNavigation(true)
            );
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

        methods: {
            ...mapActions(["fetchStudent", "updateCharon", "updateSubmission"]),

            getSubmission() {
                const userId = this.student ? this.student.id : null;
                Submission.findById(this.$route.params.submission_id, userId, submission => {
                        this.updateSubmission({submission});
                        const charonId = submission.charon_id;

                        this.charons.forEach(charon => {
                            if (charon.id === charonId) {
                                this.updateCharon({charon});
                            }
                        });

                        const studentId = submission.user_id;
                        const courseId = this.courseId;

                        this.fetchStudent({studentId, courseId});
                        this.$forceUpdate();
                    }
                );
            },

            guardFromNavigation(state) {
                this.guard_navigation = state;
            }
        }
    };
</script>
