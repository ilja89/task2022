export default class CourseSettingsForm {
    constructor(course_id, settings) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : ''
        };

        this.course_id = course_id;
    }
}
