class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving defense registrations.\n' + error, 'danger')
        })
    }

    static filtered(courseId, after, before, teacher_id, progress, session, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/defenseRegistrations/${after}/${before}/${teacher_id}/${progress}?session=${session}`)
            .then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving filtered defense registrations.\n' + error, 'danger')
        })
    }

    static getTeacherForStudent(courseId, studentId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/student/' + studentId + '/teacher')
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving student teacher.\n' + error, 'danger')
            })
    }

    static registerByTeacher(charonId, studentId, defenseLabId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/register/teacher?user_id=${studentId}`, {
            charon_id: charonId,
            defense_lab_id: defenseLabId,
        }).then(response => {
            then(response.data);
        }).catch(error => {
            this.throwError(error, 'Error creating a new defense registration.\n');
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
            this.throwError(error, 'Error creating a new defense registration.\n');
            VueEvent.$emit('student-register-end-loading');
        });
    }

    static updateRegistration(courseId, defenseId, progress, teacherId, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/registration/' + defenseId, {
            progress: progress,
            teacher_id: teacherId
        }).then(response => {
            then(response.data)
        }).catch(error => {
            this.throwError(error, 'Error updating defense registration.\n');
            then(null)
        })
    }

    static deleteStudentRegistration(charon_id, studentId, defense_lab_id, submission_id, then) {
        axios.delete('/mod/charon/api/charons/' + charon_id + `/registration?user_id=${studentId}&defLab_id=${defense_lab_id}&submission_id=${submission_id}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
                this.throwError(error, 'Error deleting defense registration.\n');
            })
    }

    static throwError(error, errorText) {
        VueEvent.$emit('show-notification',
            error.response && error.response.data && error.response.data.title
                ? error.response.data.title + ' ' + error.response.data.detail
                : errorText + error, 'danger');
    }

}

export default Defense