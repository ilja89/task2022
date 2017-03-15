import './bootstrap';

import InstanceForm from './components/instanceForm/InstanceForm.vue';
import { InstanceForm as InstanceFormForm } from './classes';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',
    components: { InstanceForm },
    data: {
        form: new InstanceFormForm(instance, gradeTypes, testerTypes, gradingMethods, courseSettings, presets)
    }
});
