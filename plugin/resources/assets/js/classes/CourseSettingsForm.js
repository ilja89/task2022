export default class CourseSettingsForm {
    constructor(courseId, settings, testerTypes, presets, gradingMethods, gradeNamePrefixes, plagiarism_settings) {
        this.fields = {
            unittests_git: settings['unittests_git'] ? settings['unittests_git'] : '',
            tester_type_code: settings['tester_type_code'] ? settings['tester_type_code'] : '',
            tester_url: settings['tester_url'] ? settings['tester_url'] : '',
            tester_token: settings['tester_token'] ? settings['tester_token'] : '',
            tester_sync_url: settings['tester_sync_url'] ? settings['tester_sync_url'] : '',
            plagiarism_language: plagiarism_settings['language'] ?? '',
            plagiarism_gitlab_group: plagiarism_settings['gitlab_group'] ?? '',
            plagiarism_project_location: plagiarism_settings['project_location'] ?? '',
            plagiarism_file_extensions: plagiarism_settings['file_extensions']?.join() ?? '',
            plagiarism_moss_passes: plagiarism_settings['moss_passes'] ?? 10,
            plagiarism_moss_matches_shown: plagiarism_settings['moss_matches_shown'] ?? 25,
            plagiarism_connection: plagiarism_settings['plagiarism_connection'] ?? false,
            plagiarism_course_exists: plagiarism_settings['course_exists'] ?? false
        };

        this.course_id = courseId;
        this.tester_types = testerTypes;
        this.grading_methods = gradingMethods;
        this.presets = presets;
        this.grade_name_prefixes = gradeNamePrefixes;
        this.plagiarism_languages = plagiarism_settings['languages'] ?? [];
        this.plagiarism_gitlab_groups = plagiarism_settings['gitlab_groups'] ?? [];
        this.plagiarism_project_locations = plagiarism_settings['project_locations'] ?? [];
    }
}
