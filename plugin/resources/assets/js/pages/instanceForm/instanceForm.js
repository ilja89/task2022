import '../../bootstrap'
import Vue from "vue"
import InstanceForm from './InstanceForm.vue'
import { InstanceForm as InstanceFormForm } from '../../classes'
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'
import 'vue-good-table/dist/vue-good-table.css';
import VueMaterial from 'vue-material'
import 'vue-material/dist/vue-material.min.css'
import 'vue-material/dist/theme/default.css'
import light from "../popup/theme"

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

    icons: {
        iconfont: 'mdi',
    },

    components: { InstanceForm },
    data: {
        form: new InstanceFormForm(
            instance,
            testerTypes,
            gradingMethods,
            courseSettings,
            presets,
            groups,
            groupings,
            plagiarismServices
            // ...
        )
    },
});
