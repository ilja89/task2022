import './bootstrap';

import InstanceForm from './components/instanceForm/InstanceForm.vue';
import InstanceFormForm from './classes/instanceForm';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',
    components: { InstanceForm },
    data: {
        form: new InstanceFormForm(instance),
        grade_types: gradeTypes,
        grading_methods: gradingMethods,
        tester_types: testerTypes
    }
});
