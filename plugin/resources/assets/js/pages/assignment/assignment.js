import '../../bootstrap'
import Vue from 'vue'
import { HighlightDirective } from '../../directives'
import { SubmissionsList, SubmissionModal } from './components'
import Vuetify from "vuetify";
import VueMaterial from 'vue-material'
import light from "../popup/theme";

Vue.directive('highlightjs', HighlightDirective);
Vue.use(VueMaterial)
Vue.use(Vuetify)

import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

const opts = {
    theme: {
        themes: {light},
    },
}

export default new Vuetify(opts)

const app = new Vue({

    el: '#app',
    vuetify: new Vuetify(),

    components: { SubmissionsList, SubmissionModal },

    icons: {
        iconfont: 'mdi', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4'
    },
    iconfont: 'mdi',

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
