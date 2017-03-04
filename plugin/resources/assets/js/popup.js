import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router';
import router from './popup/routes';

import Popup from './popup/Popup.vue';

import PopupContext from './classes/popupContext';

Vue.use(VueRouter);

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',

    components: { Popup },

    data: {
        context: new PopupContext(window.course_id),
    },
    //
    // mounted() {
    //     this.registerEventListeners();
    // },
    //
    // methods: {
    //     registerEventListeners() {
    //         VueEvent.$on('student-was-changed', student => {
    //             this.context.active_student = student;
    //             this.context.active_submission = null;
    //         });
    //         VueEvent.$on('charon-was-changed', charon => {
    //             this.context.active_charon = charon;
    //             this.context.active_submission = null;
    //         });
    //         VueEvent.$on('submission-was-selected', submission => {
    //             this.context.active_submission = submission;
    //         });
    //         VueEvent.$on('change-page', pageName => {
    //             this.context.active_page = pageName;
    //         });
    //         VueEvent.$on('show-notification', message => {
    //             this.notification_text = message;
    //             this.notification_show = true;
    //             setTimeout(() => {
    //                 this.notification_show = false;
    //             }, 2000);
    //         });
    //         VueEvent.$on('close-notification', () => this.notification_show = false);
    //         VueEvent.$on('show-loader', () => this.loaderVisible += 1);
    //         VueEvent.$on('hide-loader', () => this.loaderVisible -= 1);
    //     }
    // },

    router
});
