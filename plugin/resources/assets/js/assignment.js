import Vue from 'vue';
import moment from 'moment';
import axios from 'axios';

import { HighlightDirective } from './directives';
import SubmissionsList from './components/assignment/SubmissionsList.vue';
import SubmissionModal from './components/assignment/SubmissionModal.vue';

window.Vue = Vue;
window.moment = moment;
window.axios = axios;

Vue.directive('highlightjs', HighlightDirective);
window.VueEvent = new Vue();

const app = new Vue({

    el: '#app',

    components: { SubmissionsList, SubmissionModal },

    data: {
        submissions: submissions,
        grademaps: grademaps,
        activeSubmission: null,
    },

    methods: {
        showModal(submission) {
            this.activeSubmission = submission;
        },

        hideModal() {
            this.activeSubmission = null;
        }
    }
});
