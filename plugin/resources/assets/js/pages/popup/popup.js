import '../../bootstrap'
import Vue from 'vue'
import axios from 'axios'
import Popup from './Popup.vue'
import router from './routes'
import store from './store'
import Vuetify from 'vuetify'
import '@fortawesome/fontawesome-free/css/all.css'
import 'material-design-icons-iconfont/dist/material-design-icons.css'
import 'vuetify/dist/vuetify.min.css'
import '@mdi/font/css/materialdesignicons.css'
import VueClipboard from 'vue-clipboard2'

import {HighlightDirective} from './../../directives';
import VueApexCharts from 'vue-apexcharts';
import VueGoodTablePlugin from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.css';
import VueJsonToCsv from 'vue-json-to-csv';

Vue.use(VueClipboard)

Vue.use(Vuetify)
const opts = {}
export default new Vuetify(opts)

Vue.directive('highlightjs', HighlightDirective);

Vue.component('apexcharts', VueApexCharts);

Vue.use(VueGoodTablePlugin);

Vue.component('vue-json-to-csv', VueJsonToCsv);

window.axiosNoLoading = axios.create();

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),

    components: {Popup},

    mounted() {
        this.$store.dispatch('initializeCourse', {courseId: window.course_id})
    },

    icons: {
        iconfont: 'fa',
    },

    router,
    store,
});
