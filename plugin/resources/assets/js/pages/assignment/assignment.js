import '../../bootstrap'
import * as Vue from 'vue'
import {HighlightDirective} from '../../directives'
import AssignmentView from "./components/AssignmentView";
import CodeEditor from "./components/CodeEditor";
import CharonTabs from "../../components/partials/CharonTabs";
import CharonTab from "../../components/partials/CharonTab";
import GradesCheckboxes from "../../components/form/GradesCheckboxes";
import Vuetify from "vuetify";
import store from './store'
import VueMaterial from 'vue-material'
import light from "../popup/theme";
import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'
import CodeTemplates from "./components/CodeTemplates";

Vue.createApp(app).directive('highlightjs', HighlightDirective);
Vue.createApp(app).use(VueMaterial)

Vue.createApp(app).use(Vuetify)

const opts = {
    theme: {
        themes: {light},
    },
}

export default new Vuetify(opts)

const app = new Vue({

    el: '#app',
    vuetify: new Vuetify(),

    components: {AssignmentView, CodeEditor, CharonTabs, CharonTab, GradesCheckboxes, CodeTemplates},

    icons: {
        iconfont: 'mdi', // 'mdi' || 'mdiSvg' || 'md' || 'fa' || 'fa4'
    },
    iconfont: 'mdi',

    data: {
        grademaps: window.grademaps,
        charonId: window.charonId,
        studentId: window.studentId,

        language: language,
        editorSet: editorSet,
    },

    store
});
