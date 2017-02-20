import Vue from 'vue';
import moment from 'moment';

import SubmissionsList from './components/assignment/SubmissionsList.vue';

window.Vue = Vue;
window.moment = moment;

window.VueEvent = new Vue();

const app = new Vue({

    el: '#app',

    components: { SubmissionsList },

    data: {
        submissions: submissions,
        grademaps: grademaps
    }
});
