export default class InstanceFormForm {
    constructor(instance) {
        this.fields = {
            name: instance['name'] ? instance['name'] : '',
            project_folder: instance['project_folder'] ? instance['project_folder'] : '',
            extra: instance['extra'] ? instance['extra'] : '',

            tester_type: instance['tester_type_code'] ? instance['tester_type_code'] : 1,
            grading_method: instance['grading_method_code'] ? instance['grading_method_code'] : 1,

            grades: instance['grades'] ? instance['grades'] : []
        }
    }
}
