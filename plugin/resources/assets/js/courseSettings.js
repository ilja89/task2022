import './bootstrap';

import CourseSettingsForm from './components/courseSettings/CourseSettingsForm.vue';
import CourseSettingsFormForm from './classes/courseSettingsForm';

window.VueEvent = new Vue();

const app = new Vue({
    el: '#app',
    components: { CourseSettingsForm },
    data: {
        form: new CourseSettingsFormForm(course_id, settings, tester_types, presets)
    },
    methods: {
        getCsrfToken() {
            return window.csrf_token;
        }
    }
});
