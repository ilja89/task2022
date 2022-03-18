export default class CourseSettingsForm {
    constructor(courseId, settings, testerTypes, presets, gradingMethods, gradeNamePrefixes, plagiarism_lang_types, gitlab_location_types, gitlab_group_types) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : '',
            tester_type_code: settings['tester_type_code'] ? settings['tester_type_code'] : '',
            tester_url: settings['tester_url'] ? settings['tester_url'] : '',
            tester_token: settings['tester_token'] ? settings['tester_token'] : '',
            tester_sync_url: settings['tester_sync_url'] ? settings['tester_sync_url'] : '',
            plagiarism_language_type_code: settings['plagiarism_language_type_code'] ? settings['plagiarism_language_type_code'] : '',
            plagiarism_gitlab_group: settings['plagiarism_gitlab_group'] ? settings['plagiarism_gitlab_group'] : '',
            gitlab_location_type_code: settings['gitlab_location_type_code'] ? settings['gitlab_location_type_code'] : '',
            plagiarism_file_extensions: settings['plagiarism_file_extensions'] ? settings['plagiarism_file_extensions'].split(',') : [],
            plagiarism_moss_passes: settings['plagiarism_moss_passes'] ? settings['plagiarism_moss_passes'] : 10,
            plagiarism_moss_matches_shown: settings['plagiarism_moss_matches_shown'] ? settings['plagiarism_moss_matches_shown'] : 25,
        };

        this.course_id = courseId;
        this.tester_types = testerTypes;
        this.grading_methods = gradingMethods;
        this.presets = presets;
        this.grade_name_prefixes = gradeNamePrefixes;
        this.plagiarism_lang_types = plagiarism_lang_types;
        this.gitlab_location_types = gitlab_location_types;
        this.gitlab_group_types = gitlab_group_types;
    }
}
