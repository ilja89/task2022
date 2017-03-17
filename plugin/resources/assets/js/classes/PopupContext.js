import { Submission } from '../models';

export default class PopupContext {
    constructor(course_id) {
        this.course_id = course_id;

        this.active_student = null;
        this.active_charon = null;
        this.active_submission = null;

        this.initializeEventListeners();
    }

    initializeEventListeners() {
        VueEvent.$on('submission-was-selected', submission => {
            this.active_submission = submission
        });
        VueEvent.$on('submission-was-saved', () => {
            Submission.findById(this.active_charon.id, this.active_submission.id, submission => {
                this.active_submission = submission;
            });
        });
    }
}
