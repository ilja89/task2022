import './bootstrap';

import PopupHeader from './components/popup/PopupHeader.vue';
import PopupNavigation from './components/popup/PopupNavigation.vue';
import PopupPage from './components/popup/partials/PopupPage.vue';

import Loader from './components/popup/partials/Loader.vue';

import PopupContext from './classes/popupContext';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',

    components: { PopupHeader, PopupNavigation, PopupPage, Loader },

    data: {
        context: new PopupContext(window.course_id)
    },

    mounted() {
        this.registerEventListeners();
    },

    methods: {
        registerEventListeners() {
            VueEvent.$on('student-was-changed', (student) => {
                this.context.active_student = student;
            });
        }
    }
});

