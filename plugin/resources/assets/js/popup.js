import './bootstrap';

import PopupHeader from './components/popup/PopupHeader.vue';
import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupPage from './components/popup/partials/PopupPage.vue';
import NoStudentSelectedPage from './components/popup/NoStudentSelectedPage.vue';
import GradingPage from './components/popup/GradingPage.vue';
import SubmissionPage from './components/popup/SubmissionPage.vue';
import Loader from './components/popup/partials/Loader.vue';
import Notification from './components/popup/partials/Notification.vue';

import Api from './classes/api';
import ApiCalls from './mixins/apiCalls';

import PopupContext from './classes/popupContext';
import Submission from "./models/Submission";

window.VueEvent = new Vue();
window.Api = new Api();

const app = new Vue({
    el: '#app',

    mixins: [ ApiCalls ],

    components: { PopupHeader, PopupNavigation, PopupPage, NoStudentSelectedPage, GradingPage, Loader, SubmissionPage,
                  Notification },

    data: {
        context: new PopupContext(window.course_id),
        notification_text: '',
        notification_show: false,
        loaderVisible: 0
    },

    mounted() {
        this.registerEventListeners();
        this.initializeCharons();
    },

    methods: {
        registerEventListeners() {
            VueEvent.$on('student-was-changed', (student) => {
                this.context.active_student = student;
                this.refreshComments();
                this.context.active_submission = null;
            });
            VueEvent.$on('charon-was-changed', charon => {
                this.context.active_charon = charon;
                this.refreshComments();
                this.context.active_submission = null;
            });
            VueEvent.$on('submission-was-selected', (submission) => {
                this.context.active_submission = submission;
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
            VueEvent.$on('show-notification', message => {
                this.notification_text = message;
                this.notification_show = true;
                let that = this;
                setTimeout(() => {
                    that.notification_show = false;
                }, 2000);
            });
            VueEvent.$on('close-notification', () => this.notification_show = false);
            VueEvent.$on('show-loader', () => this.loaderVisible += 1);
            VueEvent.$on('hide-loader', () => this.loaderVisible -= 1);
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

        updateSubmission(submission) {
            let vuePopup = this;
            return new Promise((resolve, reject) => {
                this.updateSubmissionResults(this.context.active_charon.id, submission)
                    .then(response => {
                        if (response.status == "OK") {
                            submission.confirmed = 1;
                            VueEvent.$emit('show-notification', 'Submission saved!');
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

        updateActiveFile() {
            if (this.context.active_submission !== null && this.context.active_submission.files.length > 0) {
                this.context.active_file = this.context.active_submission.files[0];
            }
        },

        refreshPage() {
            this.refreshCharons();
            this.refreshComments();
        },

        refreshCharons() {
            this.getCharonsForCourse(this.context.course_id)
                .then(charons => this.context.charons = charons);
        },

        refreshComments() {
            if (this.context.active_charon !== null && this.context.active_student !== null) {
                this.getComments(this.context.active_charon.id, this.context.active_student.id)
                    .then(comments => this.context.active_comments = comments);
            }
        },
    }
});

