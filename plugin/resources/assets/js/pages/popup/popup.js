import '../../bootstrap'
import Vue from 'vue'
import axios from 'axios'

import Popup from './Popup.vue'
import router from './routes'
import store from './store'

import { HighlightDirective } from './../../directives'

Vue.directive('highlightjs', HighlightDirective)
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
