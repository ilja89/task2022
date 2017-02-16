export default class PopupContext {
    constructor(course_id) {
        this.course_id = course_id;

        this.active_student = null;
        this.active_charon = null;
        this.active_submission = null;

        this.active_page = 'Grading';
    }
}
