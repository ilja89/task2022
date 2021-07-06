import '../../bootstrap'
import Vue from 'vue'
import {HighlightDirective} from '../../directives'
import AssignmentView from "./components/AssignmentView";
import CodeEditor from "./components/CodeEditor";
import Vuetify from "vuetify";
import store from './store'
import VueMaterial from 'vue-material'
import light from "../popup/theme";
import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'

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

    components: {AssignmentView, CodeEditor},

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
