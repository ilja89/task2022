import '../../bootstrap'
import * as Vue from 'vue'
import axios from 'axios'
import Popup from './Popup.vue'
import router from './routes'
import store from './store'
import Vuetify from 'vuetify'
import VueClipboard from 'vue-clipboard2'
import light from './theme'
import {HighlightDirective} from './../../directives';
import VueApexCharts from 'vue-apexcharts';
import VueGoodTablePlugin from 'vue-good-table';
import VueJsonToCsv from 'vue-json-to-csv';
import VueMaterial from 'vue-material'

Vue.createApp(app).use(VueMaterial)
Vue.createApp(app).use(VueClipboard)
Vue.createApp(app).use(Vuetify)

import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css';
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

const opts = {
    theme: {
        themes: {light},
    },
}

export default new Vuetify(opts)

Vue.createApp(app).directive('highlightjs', HighlightDirective);
Vue.createApp(app).component('apexcharts', VueApexCharts);
Vue.createApp(app).use(VueGoodTablePlugin);
Vue.createApp(app).component('vue-json-to-csv', VueJsonToCsv);

window.axiosNoLoading = axios.create();

const app = Vue.createApp({
    el: '#app',
    vuetify: new Vuetify(),

    components: {Popup},

    mounted() {
        this.$store.dispatch('initializeCourse', {courseId: window.course_id})
    },

    icons: {
        iconfont: 'mdi', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4'
    },
    iconfont: 'mdi',

    router,
    store,
});
