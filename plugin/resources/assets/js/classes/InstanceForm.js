import moment from 'moment';

export default class InstanceFormForm {
    constructor(instance, tester_types, grading_methods, courseSettings, presets, groups, groupings, plagiarism_services) {
        this.initializeFields(instance, courseSettings);
        this.tester_types = tester_types;
        this.grading_methods = grading_methods;
        this.presets = presets;
        this.groups = groups;
        this.groupings = groupings;
        this.plagiarism_services = plagiarism_services;
        this.groups.unshift({ id: null, name: 'All groups' });
        this.recalculate_grades = false;
    }

    activateGrademap(grade_type_code) {
        let grademap = {
            max_points: 0,
            name: '',
            grade_type_code: grade_type_code,
            id_number: ''
        };
        this.fields.grademaps.push(grademap);

        this.fields.grademaps = this.fields.grademaps.sort((a, b) => {
            return a.grade_type_code > b.grade_type_code ? 1 : -1;
        });

        return grademap;
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
                id_number: grademap.grade_item.idnumber
            });
        });
    }

    initializeDeadlines(deadlines) {
        deadlines.forEach((deadline) => {
            // Check if previous deadline exists, if it matches format from database, if it matches
            // format from previous request.
            let time = null;

            if (deadline.deadline_time !== null) {
                if (moment(deadline.deadline_time.date, 'YYYY-MM-DD HH:mm:ss').isValid()) {
                    time = moment(deadline.deadline_time.date, 'YYYY-MM-DD HH:mm:ss');
                } else if (moment(deadline.deadline_time, 'DD-MM-YYYY HH:mm').isValid()) {
                    time = moment(deadline.deadline_time, 'DD-MM-YYYY HH:mm');
                }
            }

            if (time !== null) {
                let deadline_thing = {
                    deadline_time: {
                        time: time.format('DD-MM-YYYY HH:mm')
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
            name: instance['name'] ? instance['name'] : '',
            project_folder: instance['project_folder'] ? instance['project_folder'] : '',
            tester_extra: instance['tester_extra'] ? instance['tester_extra'] : '',
            system_extra: instance['system_extra'] ? instance['system_extra'] : '',
            calculation_formula: instance['calculation_formula'] ? instance['calculation_formula'] : '',
            max_score: instance['max_score'] ? parseFloat(instance['max_score']).toFixed(2) : '',

            tester_type: instance['tester_type_code']
                ? instance['tester_type_code']
                : (courseSettings['tester_type_code'] ? courseSettings['tester_type_code'] : 1),
            grading_method: instance['grading_method_code'] ? instance['grading_method_code'] : 1,
            grouping_id: instance['grouping_id'] ? instance['grouping_id'] : null,

            grademaps: [ ],
            deadlines: [ ],

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
            instance['grademaps'] ? this.initializeGrademaps(instance['grademaps']) : '' ;
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
                    id_number: grademap.id_number
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
}
