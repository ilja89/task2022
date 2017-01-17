export default class InstanceFormForm {
    constructor(instance, grade_types, tester_types, grading_methods) {
        this.initializeFields(instance);

        this.grade_types = grade_types;
        this.tester_types = tester_types;
        this.grading_methods = grading_methods;
    }

    activateGrademap(grade_type_code) {
        this.fields.grademaps.push({
            max_points: 0,
            name: '',
            grade_type_code: grade_type_code
        });

        this.fields.grademaps.sort((a, b) => {
            return a.grade_type_code > b.grade_type_code ? 1 : -1;
        });
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

    initializeFields(instance) {
        this.fields = {
            name: instance['name'] ? instance['name'] : '',
            project_folder: instance['project_folder'] ? instance['project_folder'] : '',
            extra: instance['extra'] ? instance['extra'] : '',

            tester_type: instance['tester_type_code'] ? instance['tester_type_code'] : 1,
            grading_method: instance['grading_method_code'] ? instance['grading_method_code'] : 1,

            grademaps: instance['grademaps'] ? instance['grademaps'] : [ ],
            deadlines: instance['deadlines'] ? instance['deadlines'] : [ ]
        };

    }
}
