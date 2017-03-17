import Vue from 'vue'
import moment from 'moment'
import axios from 'axios'
import { HighlightDirective } from '../../directives'
import SubmissionsList from './components/SubmissionsList.vue'
import SubmissionModal from './components/SubmissionModal.vue'

window.Vue = Vue;
window.moment = moment;
window.axios = axios;

Vue.directive('highlightjs', HighlightDirective);
window.VueEvent = new Vue();

const app = new Vue({

    el: '#app',

    components: { SubmissionsList, SubmissionModal },

    data: {
        grademaps: grademaps,
        activeSubmission: null,
        charonId: charonId,
        studentId: studentId,
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
