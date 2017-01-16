export default class InstanceFormForm {
    constructor(instance, grade_types, tester_types, grading_methods) {
        this.fields = {
            name: instance['name'] ? instance['name'] : '',
            project_folder: instance['project_folder'] ? instance['project_folder'] : '',
            extra: instance['extra'] ? instance['extra'] : '',

            tester_type: instance['tester_type_code'] ? instance['tester_type_code'] : 1,
            grading_method: instance['grading_method_code'] ? instance['grading_method_code'] : 1,

            grades: instance['grades'] ? instance['grades'] : [
                    {
                        max_points: 1,
                        name: '',
                        grade_type_code: 1
                    }
                ]
        };

        this.grade_types = grade_types;
        this.tester_types = tester_types;
        this.grading_methods = grading_methods;
    }
}
