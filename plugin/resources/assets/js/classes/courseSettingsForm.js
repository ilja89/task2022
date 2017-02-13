export default class CourseSettingsForm {
    constructor(courseId, settings, testerTypes) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : '',
            tester_type: settings['tester_type_code'] ? settings['tester_type_code'] : '',
        };

        this.course_id = courseId;
        this.tester_types = testerTypes;
    }
}
