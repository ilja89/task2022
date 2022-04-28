import '../../bootstrap'
import Vue from 'vue'
import {HighlightDirective} from '../../directives'
import AssignmentView from "./components/AssignmentView.vue";
import CodeEditor from "./components/CodeEditor.vue";
import CharonTabs from "../../components/partials/CharonTabs.vue";
import CharonTab from "../../components/partials/CharonTab.vue";
import GradesCheckboxes from "../../components/form/GradesCheckboxes.vue";
import Vuetify from "vuetify";
import store from './store'
import router from './routes'
import VueMaterial from 'vue-material'
import light from "../popup/theme";
import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'
import CodeTemplates from "./components/CodeTemplates.vue";
import ToggleButton from "../../components/partials/ToggleButton.vue";

Vue.directive('highlightjs', HighlightDirective);
Vue.use(VueMaterial)

Vue.use(Vuetify)

const opts = {
    theme: {
        themes: {light},
    },
}

export default new Vuetify(opts)

const app = new Vue({

    el: '#app',
    vuetify: new Vuetify(),

    components: {AssignmentView, CodeEditor, CharonTabs, CharonTab, GradesCheckboxes, CodeTemplates, ToggleButton},

    icons: {
        iconfont: 'mdi', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4'
    },
    iconfont: 'mdi',

    data: {
        grademaps: window.grademaps,
        charonId: window.charonId,
        studentId: window.studentId,

        allow_submission: allow_submission,
        language: language,
    },

    router,
    store
});
