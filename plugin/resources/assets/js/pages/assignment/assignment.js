import '../../bootstrap'
import Vue from 'vue'
import moment from 'moment'
import { HighlightDirective } from '../../directives'
import { SubmissionsList, SubmissionModal } from './components'
import Vuetify from "vuetify";

window.moment = moment;
Vue.use(Vuetify)

Vue.directive('highlightjs', HighlightDirective);

const app = new Vue({

    el: '#app',
    vuetify: new Vuetify(),

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
        },
    },
});
