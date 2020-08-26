import '../../bootstrap';

import InstanceForm from './InstanceForm.vue';
import { InstanceForm as InstanceFormForm } from '../../classes';
import Vuetify from "vuetify";

const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(),

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
