import '../../bootstrap';

import InstanceForm from './InstanceForm.vue';
import { InstanceForm as InstanceFormForm } from '../../classes';

const app = new Vue({
    el: '#app',
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
