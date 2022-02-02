export default class CourseSettingsForm {
    constructor(courseId, settings, testerTypes, presets, gradingMethods, gradeNamePrefixes, queryLoggingTypes) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : '',
            tester_type_code: settings['tester_type_code'] ? settings['tester_type_code'] : '',
            tester_url: settings['tester_url'] ? settings['tester_url'] : '',
            tester_token: settings['tester_token'] ? settings['tester_token'] : '',
            tester_sync_url: settings['tester_sync_url'] ? settings['tester_sync_url'] : '',
            query_logging_code: settings['query_logging'],
        };

        this.course_id = courseId;
        this.tester_types = testerTypes;
        this.grading_methods = gradingMethods;
        this.presets = presets;
        this.grade_name_prefixes = gradeNamePrefixes;
        this.query_logging_types = queryLoggingTypes;
    }
}
