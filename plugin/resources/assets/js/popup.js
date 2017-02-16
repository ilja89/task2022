import './bootstrap';

import PopupHeader from './components/popup/PopupHeader.vue';

import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupPage from './components/popup/partials/PopupPage.vue';

import NoStudentSelectedPage from './components/popup/NoStudentSelectedPage.vue';
import GradingPage from './components/popup/GradingPage.vue';
import SubmissionPage from './components/popup/SubmissionPage.vue';

import Loader from './components/popup/partials/Loader.vue';
import Notification from './components/popup/partials/Notification.vue';

import PopupContext from './classes/popupContext';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',

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
    },

    methods: {
        registerEventListeners() {
            VueEvent.$on('student-was-changed', student => {
                this.context.active_student = student;
                this.context.active_submission = null;
            });
            VueEvent.$on('charon-was-changed', charon => {
                this.context.active_charon = charon;
                this.context.active_submission = null;
            });
            VueEvent.$on('submission-was-selected', submission => {
                this.context.active_submission = submission;
            });
            VueEvent.$on('file-was-changed', file => {
                this.context.active_file = file;
            });
            VueEvent.$on('change-page', pageName => {
                this.context.active_page = pageName;
            });
            VueEvent.$on('show-notification', message => {
                this.notification_text = message;
                this.notification_show = true;
                setTimeout(() => {
                    this.notification_show = false;
                }, 2000);
            });
            VueEvent.$on('close-notification', () => this.notification_show = false);
            VueEvent.$on('show-loader', () => this.loaderVisible += 1);
            VueEvent.$on('hide-loader', () => this.loaderVisible -= 1);
        }
    }
});

