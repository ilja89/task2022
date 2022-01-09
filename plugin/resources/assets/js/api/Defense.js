class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving defense registrations.\n' + error, 'danger')
        })
    }

    static filtered(courseId, after, before, teacher_id, progress, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/' + after + '/' + before + '/' + teacher_id + '/' + progress)
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

    static registerByTeacher(charonId, studentId, defenseLabId, progress, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/register/teacher?user_id=${studentId}`, {
            charon_id: charonId,
            defense_lab_id: defenseLabId,
            progress: progress,
        }).then(response => {
            then(response.data);
        }).catch(error => {
            VueEvent.$emit('show-notification',
                error.response && error.response.data && error.response.data.title
                    ? error.response.data.title + ' ' + error.response.data.detail
                    : 'Error creating a new defense registration.\n' + error, 'danger');
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
            VueEvent.$emit('show-notification',
                error.response && error.response.data && error.response.data.title
                    ? error.response.data.title + ' ' + error.response.data.detail
                    : 'Error creating a new defense registration.\n' + error, 'danger');
            VueEvent.$emit('student-register-end-loading');
        });
    }

    static updateRegistration(courseId, defenseId, progress, teacher_id, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/registration/' + defenseId, {
            progress: progress,
            teacher_id: teacher_id
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving defense progress.\n' + error, 'danger')
        })
    }

    static updateRegistrationAndUndefendRegistrationsByTeacher(courseId, defenseId, registrationNewProgress, activeRegistrationsProgress, lab_id, teacher_id, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/registration/' + defenseId + '/update/undefend', {
            activeRegistrationsProgress: activeRegistrationsProgress,
            registrationNewProgress: registrationNewProgress,
            lab_id: lab_id,
            teacher_id: teacher_id
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving defense progress.\n' + error, 'danger')
        })
    }

    static deleteStudentRegistration(charon_id, studentId, defense_lab_id, submission_id, then) {
        axios.delete('/mod/charon/api/charons/' + charon_id + `/registration?user_id=${studentId}&defLab_id=${defense_lab_id}&submission_id=${submission_id}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting student registration.\n' + error, 'danger')
        })
    }

    static getByTeacher(course_id, teacher_id, lab_id, then) {
        axios.get(`/mod/charon/api/courses/${course_id}/defenseRegistrations/teacher?teacher_id=${teacher_id}&lab_id=${lab_id}`).then(response => {
            then(response.data)
        })
    }

}

export default Defense