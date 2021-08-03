export default class CourseSettingsForm {
    constructor(courseId, settings, testerTypes, presets, gradingMethods, gradeNamePrefixes) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : '',
            tester_type_code: settings['tester_type_code'] ? settings['tester_type_code'] : '',
            tester_url: settings['tester_url'] ? settings['tester_url'] : '',
            tester_token: settings['tester_token'] ? settings['tester_token'] : '',
        };

        this.course_id = courseId;
        this.tester_types = testerTypes;
        this.grading_methods = gradingMethods;
        this.presets = presets;
        this.grade_name_prefixes = gradeNamePrefixes;
    }
}
