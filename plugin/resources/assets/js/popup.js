import './bootstrap';

import PopupHeader from './components/popup/PopupHeader.vue';
import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupPage from './components/popup/partials/PopupPage.vue';
import NoStudentSelectedPage from './components/popup/NoStudentSelectedPage.vue';
import GradingPage from './components/popup/GradingPage.vue';
import SubmissionPage from './components/popup/SubmissionPage.vue';
import Loader from './components/popup/partials/Loader.vue';

import Api from './classes/api';
import ApiCalls from './mixins/apiCalls';

import PopupContext from './classes/popupContext';

window.VueEvent = new Vue();
window.Api = new Api();

const app = new Vue({
    el: '#app',

    mixins: [ ApiCalls ],

    components: { PopupHeader, PopupNavigation, PopupPage, NoStudentSelectedPage, GradingPage, Loader, SubmissionPage },

    data: {
        context: new PopupContext(window.course_id)
    },

    mounted() {
        this.registerEventListeners();
        this.initializeCharons();
    },

    methods: {
        registerEventListeners() {
            VueEvent.$on('student-was-changed', (student) => {
                this.context.active_student = student;
                if (this.context.active_charon !== null) {
                    this.getSubmissions(this.context.active_charon.id, student.id);
                    this.refreshComments();
                }
                this.context.active_submission = null;
            });
            VueEvent.$on('charon-was-changed', charon => {
                this.context.active_charon = charon;
                this.context.active_submission = null;

                if (this.context.active_student !== null) {
                    this.getSubmissions(charon.id, this.context.active_student.id);
                    this.refreshComments();
                }
            });
            VueEvent.$on('submission-was-selected', (submission) => {
                this.context.active_submission = submission;
                VueEvent.$emit('change-page', 'Submission');
            });
            VueEvent.$on('save-active-submission', () => {
                this.updateSubmission(this.context.active_submission);
            });
            VueEvent.$on('file-was-changed', (file) => {
                this.context.active_file = file;
            });
            VueEvent.$on('comment-was-saved', (comment) => {
                this.saveComment(comment);
            });
            VueEvent.$on('change-page', pageName => {
                this.context.active_page = pageName;
            });
            VueEvent.$on('refresh-page', this.refreshPage);
        },

        initializeCharons() {
            this.getCharonsForCourse(this.context.course_id)
                .then(charons => {
                    this.context.charons = charons;
                    if (charons.length > 0) {
                        VueEvent.$emit('charon-was-changed', charons[0]);
                    }
                });
        },

        getSubmissions(charon_id, user_id, update_submission = true) {

            let popupVue = this;
            return new Promise((resolve, reject) => {
                this.getSubmissionsForUser(charon_id, user_id)
                    .then(submissions => {
                        popupVue.context.submissions = submissions;
                        if (update_submission) {
                            popupVue.updateActiveSubmission();
                        }
                        popupVue.updateActiveFile();
                        resolve(submissions);
                    });
            });
        },

        updateSubmission(submission) {
            let vuePopup = this;
            return new Promise((resolve, reject) => {
                this.updateSubmissionResults(this.context.active_charon.id, submission)
                    .then(response => {
                        if (response.status == "OK") {
                            submission.confirmed = 1;
                        }
                        vuePopup.getSubmissions(vuePopup.context.active_charon.id, vuePopup.context.active_student.id, false);
                        resolve(response);
                    })
                    .catch(error => reject(error));
            });
        },

        saveComment(comment) {
            let vuePopup = this;
            this.saveCharonComment(this.context.active_charon.id, this.context.active_student.id, comment)
                .then(response => {
                    vuePopup.context.active_comments.push(response.comment);
                });
        },

        updateActiveSubmission() {
            if (this.context.submissions.length > 0) {
                this.context.active_submission = this.context.submissions[0];
            }
        },

        updateActiveFile() {
            if (this.context.active_submission !== null && this.context.active_submission.files.length > 0) {
                this.context.active_file = this.context.active_submission.files[0];
            }
        },

        refreshPage() {
            this.refreshCharons();
            this.refreshComments();
            this.refreshSubmissions();
        },

        refreshCharons() {
            this.getCharonsForCourse(this.context.course_id)
                .then(charons => this.context.charons = charons);
        },

        refreshComments() {
            this.getComments(this.context.active_charon.id, this.context.active_student.id)
                .then(comments => this.context.active_comments = comments);
        },

        refreshSubmissions() {
            let vuePopup = this;
            this.getSubmissions(this.context.active_charon.id, this.context.active_student.id, false)
                .then(submissions => {
                    submissions.forEach(submission => {
                        if (vuePopup.context.active_submission.id == submission.id) {
                            vuePopup.context.active_submission = submission;
                        }
                    });
                });
        }
    }
});

