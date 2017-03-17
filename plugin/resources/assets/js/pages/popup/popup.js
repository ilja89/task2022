import '../../bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router';
import router from './routes';

import Popup from './Popup.vue';

import { PopupContext } from '../../classes';
import { HighlightDirective } from './../../directives';

Vue.use(VueRouter);
Vue.directive('highlightjs', HighlightDirective);

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',

    components: { Popup },

    data: {
        context: new PopupContext(window.course_id),
    },

    router
});
