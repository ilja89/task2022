import {Error} from "./index";

class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving defense registrations.\n')
            })
    }

    static filtered(courseId, after, before, teacher_id, progress, session, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/defenseRegistrations/${after}/${before}/${teacher_id}/${progress}?session=${session}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving filtered defense registrations.\n')
            })
    }

    static getTeacherForStudent(courseId, studentId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/student/' + studentId + '/teacher')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving student teacher.\n')
            })
    }

    static registerByTeacher(charonId, studentId, defenseLabId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/register/teacher?user_id=${studentId}`, {
            charon_id: charonId,
            defense_lab_id: defenseLabId,
        }).then(response => {
            then(response.data);
        }).catch(error => {
            Error.throwWithCheck(error, 'Error creating a new defense registration.\n');
        });
    }

    static registerByStudent(charonId, studentId, defenseLabId, submissionId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/register/student?user_id=${studentId}`, {
            charon_id: charonId,
            defense_lab_id: defenseLabId,
            submission_id: submissionId,
        }).then(response => {
            then(response.data);
        }).catch(error => {
            Error.throwWithCheck(error, 'Error creating a new defense registration.\n');
            VueEvent.$emit('student-register-end-loading');
        });
    }

    static updateRegistration(courseId, defenseId, progress, teacherId, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/registration/' + defenseId, {
            progress: progress,
            teacher_id: teacherId
        }).then(response => {
            then(Promise.resolve(response.data))
        }).catch(error => {
            Error.throwWithCheck(error, 'Error updating defense registration.\n');
            then(Promise.reject(error))
        })
    }

    static deleteStudentRegistration(charon_id, studentId, defense_lab_id, submission_id, then) {
        axios.delete('/mod/charon/api/charons/' + charon_id + `/registration?user_id=${studentId}&defLab_id=${defense_lab_id}&submission_id=${submission_id}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throwWithCheck(error, 'Error deleting defense registration.\n');
            })
    }

}

export default Defense