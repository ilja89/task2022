import '../../bootstrap'
import Vue from 'vue'
import axios from 'axios'

import Popup from './Popup.vue'
import router from './routes'
import store from './store'

import { HighlightDirective } from './../../directives';
import VueApexCharts from 'vue-apexcharts';
import VueGoodTablePlugin from 'vue-good-table';
import 'vue-good-table/dist/vue-good-table.css';

Vue.directive('highlightjs', HighlightDirective);

Vue.component('apexcharts', VueApexCharts);

Vue.use(VueGoodTablePlugin);

window.axiosNoLoading = axios.create()

const app = new Vue({
    el: '#app',

    components: { Popup },

    mounted() {
        this.$store.dispatch('initializeCourse', { courseId: window.course_id })
    },

    router,
    store,
});
