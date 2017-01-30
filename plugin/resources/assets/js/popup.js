import './bootstrap';

import PopupHeader from './components/popup/PopupHeader.vue';
import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupPage from './components/popup/partials/PopupPage.vue';
import NoStudentSelectedPage from './components/popup/NoStudentSelectedPage.vue';
import GradingPage from './components/popup/GradingPage.vue';
import SubmissionPage from './components/popup/SubmissionPage.vue';

import Loader from './components/popup/partials/Loader.vue';

import PopupContext from './classes/popupContext';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',

    components: { PopupHeader, PopupNavigation, PopupPage, NoStudentSelectedPage, GradingPage, Loader, SubmissionPage },

    data: {
        context: new PopupContext(window.course_id)
    },

    mounted() {
        this.registerEventListeners();
        this.getCharonsForCourse(this.context.course_id);
    },

    methods: {
        registerEventListeners() {
            VueEvent.$on('student-was-changed', (student) => {
                this.context.active_student = student;
                if (this.context.active_charon !== null) {
                    this.getSubmissions(this.context.active_charon.id, student.id);
                }
                this.context.active_submission = null;
            });
            VueEvent.$on('charon-was-changed', (charon_id) => {
                this.context.charons.forEach((charon) => {
                    if (charon.id == charon_id) {
                        this.context.active_charon = charon;
                    }
                });
                this.context.active_submission = null;

                if (this.context.active_student !== null) {
                    this.getSubmissions(charon_id, this.context.active_student.id);
                }
            });
            VueEvent.$on('submission-was-selected', (submission) => {
                this.context.active_submission = submission;
                VueEvent.$emit('change-page', 'Submission');
            });
            VueEvent.$on('save-active-submission', () => {
                this.updateSubmission(this.context.active_submission);
            });
        },

        getCharonsForCourse(course_id) {
            let popupVue = this;
            axios.get('/mod/charon/api/courses/' + course_id + '/charons')
                .then(function (response) {
                    popupVue.context.charons = response.data;
                    if (response.data.length > 0) {
                        VueEvent.$emit('charon-was-changed', response.data[0].id);
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
        },

        getSubmissions(charon_id, user_id) {
            let popupVue = this;
            axios.get('/mod/charon/api/charons/' + charon_id + '/submissions', {
                params: {
                    user_id: user_id
                }
            })
                .then(function (response) {
                    popupVue.context.submissions = response.data;
                    if (popupVue.context.submissions.length > 0) {
                        popupVue.context.active_submission = popupVue.context.submissions[0];
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });

        },

        updateSubmission(submission) {
            axios.post('/mod/charon/api/charons/' + this.context.active_charon.id + '/submissions/' + submission.id, {
                submission: submission
            })
                .then(function (response) {
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.log(error);
                })
        }
    }
});

