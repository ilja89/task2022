import './bootstrap';

import InstanceForm from './components/instanceForm/InstanceForm.vue';
import InstanceFormForm from './classes/instanceForm';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',
    components: { InstanceForm },
    data: {
        form: new InstanceFormForm(instance, window.gradeTypes, window.testerTypes, window.gradingMethods, courseSettings)
    }
});
