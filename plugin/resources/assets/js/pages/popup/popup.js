import '../../bootstrap'
import Vue from 'vue'
import axios from 'axios'
import Popup from './Popup.vue'
import router from './routes'
import store from './store'
import Vuetify from 'vuetify'
import VueClipboard from 'vue-clipboard2'
import light from './theme'
import VueApexCharts from 'vue-apexcharts';
import VueGoodTablePlugin from 'vue-good-table';
import VueJsonToCsv from 'vue-json-to-csv';
import VueMaterial from 'vue-material'

Vue.use(VueMaterial)
Vue.use(VueClipboard)
Vue.use(Vuetify)

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
        iconfont: 'mdi', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4'
    },
    iconfont: 'mdi',

    router,
    store,
});
