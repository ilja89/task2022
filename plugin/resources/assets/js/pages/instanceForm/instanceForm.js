import '../../bootstrap'
import { createApp } from 'vue';
import InstanceForm from './InstanceForm.vue'
import { InstanceForm as InstanceFormForm } from '../../classes'
import Vuetify from "vuetify";
import VueMaterial from 'vue-material'
import light from "../popup/theme"

const app = createApp(InstanceForm, {
    el: '#app',
    vuetify: new Vuetify(),

    icons: {
        iconfont: 'mdi',
    },
    iconfont: 'mdi',

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
    }
});


app.use(VueMaterial)
app.use(Vuetify)

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
