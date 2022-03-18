import moment from 'moment';

export default class InstanceFormForm {
    constructor(instance, tester_types, grading_methods, courseSettings, presets, groups, groupings, plagiarism_services, plagiarismSettings) {
        this.initializeFields(instance, courseSettings);
        this.tester_types = tester_types;
        this.grading_methods = grading_methods;
        this.presets = presets;
        this.groups = groups;
        this.groupings = groupings;
        this.plagiarism_services = plagiarism_services;
        this.groups.unshift({id: null, name: 'All groups'});
        this.recalculate_grades = false;
        this.plagiarism_create_update_charon = false;
        this.plagiarismSettings = plagiarismSettings;
    }

    activateGrademap(grade_type_code) {
        let grademap = {
            max_points: 0,
            name: `${this.fields.project_folder} - ${this.getGradeTypeName(grade_type_code)}`,
            grade_type_code: grade_type_code,
            id_number: `${this.fields.project_folder}_${this.getGradeTypeName(grade_type_code)}`,
            persistent: 0
        };
        this.fields.grademaps.push(grademap);

        this.fields.grademaps = this.fields.grademaps.sort((a, b) => {
            return a.grade_type_code > b.grade_type_code ? 1 : -1;
        });

        return grademap;
    }

    getGradeTypeName(grade_type_code) {
        let gradeTypeName = '';
        if (grade_type_code <= 100) {
            gradeTypeName = 'Tests_' + grade_type_code;
        } else if (grade_type_code <= 1000) {
            gradeTypeName = 'Style_' + grade_type_code % 100;
        } else {
            gradeTypeName = 'Custom_' + grade_type_code % 1000;
        }

        return gradeTypeName;
    }

    deactivateGrademap(grade_type_code) {
        let removedIndex = -1;

        this.fields.grademaps.forEach((grade, index) => {
            if (grade_type_code == grade.grade_type_code) {
                removedIndex = index;
            }
        });

        this.fields.grademaps.splice(removedIndex, 1);
    }

    addDeadline() {
        this.fields.deadlines.push({
            deadline_time: {
                time: ''
            },
            percentage: 100,
            group_id: null,
        });
    }

    initializeGrademaps(grademaps) {
        grademaps.forEach((grademap) => {
            this.fields.grademaps.push({
                max_points: parseFloat(grademap.grade_item.grademax).toFixed(2),
                name: grademap.name,
                grade_type_code: grademap.grade_type_code,
                id_number: grademap.grade_item.idnumber,
                persistent: grademap.persistent
            });
        });
    }

    initializeTemplates(templates) {
        templates.forEach((template, index) => {
            this.fields.files.push({
                id: index,
                path: template.path,
                content: template.contents,
                duplicate: false
            });
        })
    }

    initializeDeadlines(deadlines) {
        deadlines.forEach((deadline) => {
            // Check if previous deadline exists, if it matches format from database, if it matches
            // format from previous request.
            let time = null;
            if (deadline.deadline_time !== null) {
                if (moment(deadline.deadline_time, 'YYYY-MM-DD HH:mm:ss').isValid()) {
                    time = moment(deadline.deadline_time, 'YYYY-MM-DD HH:mm:ss');
                } else if (moment(deadline.deadline_time, 'DD-MM-YYYY HH:mm').isValid()) {
                    time = moment(deadline.deadline_time, 'DD-MM-YYYY HH:mm');
                }
            }

            if (time !== null) {
                let deadline_thing = {
                    deadline_time: {
                        time: time.format('YYYY-MM-DD HH:mm')
                    },
                    percentage: deadline.percentage,
                    group_id: deadline.group_id
                };

                this.fields.deadlines.push(deadline_thing);
            } else {
                this.addDeadline();
            }
        });
    }

    initializeFields(instance, courseSettings) {
        this.fields = {
            // EDITOR
            course: courseSettings['course_id'],
            allow_submission: instance['allow_submission'] === null ? false : instance['allow_submission'] > 0,
            files: [],

            // MODULE INFO
            name: instance['name'] ? instance['name'] : '',

            // TESTER INFO
            tester_type_code: instance['tester_type'] ? instance['tester_type']['code']
                : (courseSettings['tester_type_code'] ? courseSettings['tester_type_code'] : 1),
            project_folder: instance['project_folder'] ? instance['project_folder'] : '',
            tester_extra: instance['tester_extra'] ? instance['tester_extra'] : '',
            unittests_git_charon: instance['unittests_git'] ? instance['unittests_git']
                : (courseSettings['unittests_git'] ? courseSettings['unittests_git'] : ''),
            system_extra: instance['system_extra'] ? instance['system_extra'] : '',
            docker_timeout: instance['docker_timeout'] ? instance['docker_timeout'] : 120,
            docker_content_root: instance['docker_content_root'] ? instance['docker_content_root'] : '',
            docker_test_root: instance['docker_test_root'] ? instance['docker_test_root'] : '',

            // GRADING INFO
            calculation_formula: instance['calculation_formula'] ? instance['calculation_formula'] : '',
            max_score: instance['max_score'] ? parseFloat(instance['max_score']).toFixed(2) : '',
            grading_method: instance['grading_method_code'] ? instance['grading_method_code'] : 1,
            grademaps: [],
            deadlines: [],
            grouping_id: instance['grouping_id'] ? instance['grouping_id'] : null,

            // DEFENCE INFO
            defense_deadline: instance['defense_deadline'] ? instance['defense_deadline'] : '',
            defense_start_time: instance['defense_start_time'] ? instance['defense_start_time'] : '',
            defense_duration: instance['defense_duration'] ? instance['defense_duration'] : 5,
            choose_teacher: instance['choose_teacher'] ? instance['choose_teacher'] : false,
            defense_threshold: instance['defense_threshold'] ? instance['defense_threshold'] : 50,
            group_size: instance['group_size'] ? instance['group_size'] : 3,

            // PLAGIARISM INFO
            plagiarism_enabled: false,
            plagiarism_services: [null],
            plagiarism_resource_providers: [
                {repository: '', private_key: ''},
            ],
            plagiarism_includes: '',

            preset: null,
        };

        if (window.update) {
            this.initializeGrademapsUpdate(instance['grademaps']);
        } else {
            instance['templates'] ? this.initializeTemplates(instance['templates']) : '';
            instance['grademaps'] ? this.initializeGrademaps(instance['grademaps']) : '';
        }
        instance['deadlines'] ? this.initializeDeadlines(instance['deadlines']) : this.addDeadline();
    }

    initializeGrademapsUpdate(grademaps) {
        /**
         * Since update grademaps are grade_type_code => grademap in the request, we must
         * handle these differently here.
         */
        for (let grade_type_code in grademaps) {
            if (grademaps.hasOwnProperty(grade_type_code)) {
                let grademap = grademaps[grade_type_code];
                this.fields.grademaps.push({
                    max_points: parseFloat(grademap.max_points),
                    name: grademap.grademap_name,
                    grade_type_code: parseInt(grade_type_code),
                    id_number: grademap.id_number,
                    persistent: grademap.persistent
                });
            }
        }
    }

    onPresetSelected(presetId) {
        this.selectCorrectPreset(presetId);
        this.updateFieldsToMatchActivePreset();
    }

    selectCorrectPreset(presetId) {
        let selectedPreset = null;
        this.presets.forEach(presetLoop => {
            if (presetLoop.id == presetId) {
                selectedPreset = presetLoop;
            }
        });
        this.fields.preset = selectedPreset;
    }

    updateFieldsToMatchActivePreset() {
        let preset = this.fields.preset;
        this.fields.tester_extra = preset.tester_extra;
        this.fields.system_extra = preset.system_extra;
        this.fields.grading_method = preset.grading_method_code;
        this.fields.max_score = preset.max_result;

        let calculationFormula = preset.calculation_formula;

        this.fields.grademaps = [];

        preset.preset_grades.forEach(presetGrade => {
            let grademap = this.activateGrademap(presetGrade.grade_type_code);
            if (presetGrade.grade_name_prefix_code == 1) {
                grademap.name = this.fields.project_folder + ' ' + presetGrade.grade_name;
            } else if (presetGrade.grade_name_prefix_code == 2) {
                grademap.name = this.fields.name + ' ' + presetGrade.grade_name;
            }
            if (presetGrade.id_number_postfix !== null) {
                grademap.id_number = this.fields.project_folder + presetGrade.id_number_postfix;
            } else {
                grademap.id_number = '';
            }
            grademap.max_points = presetGrade.max_result;

            if (calculationFormula !== null) {
                let gradeType = this.getGradeTypeFromCode(presetGrade.grade_type_code);
                let replace = "\\[\\[" + gradeType.name + "\\]\\]";
                let regex = new RegExp(replace, "g");
                calculationFormula = calculationFormula.replace(regex, "[[" + grademap.id_number + "]]");
            } else {
                calculationFormula = '';
            }
        });

        this.fields.calculation_formula = calculationFormula;
    }

    getGradeTypeFromCode(gradeTypeCode) {
        return {
            code: gradeTypeCode,
            name: this.getGradeTypeName(gradeTypeCode)
        };
    }

    addPlagiarismService() {
        this.fields.plagiarism_services.push(null);
    }

    removePlagiarismService(index) {
        this.fields.plagiarism_services.splice(index, 1);
    }
}
