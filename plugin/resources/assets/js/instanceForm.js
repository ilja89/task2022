import './bootstrap';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

import InstanceForm from './components/instanceForm/InstanceForm.vue';

window.VueEvent = new Vue();

class Errors {
    constructor() {
        this.errors = {};
    }

    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0];
        }
    }
}

class InstanceFormForm {
    constructor(instance) {
        this.fields = {
            name: instance['name'] ? instance['name'] : '',
            project_folder: instance['project_folder'] ? instance['project_folder'] : ''
        };
    }
}

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
