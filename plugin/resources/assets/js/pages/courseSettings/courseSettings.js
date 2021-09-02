import '../../bootstrap';

import CourseSettingsForm from './CourseSettingsForm.vue';
import { CourseSettingsForm as CourseSettingsFormForm } from '../../classes';
import Vue from "vue";

const app = new Vue({
    el: '#app',
    components: { CourseSettingsForm },
    data: {
        form: new CourseSettingsFormForm(course_id, settings, tester_types, presets, grading_methods, grade_name_prefixes)
    },
    methods: {
        getCsrfToken() {
            return window.csrf_token;
        }
    }
});
