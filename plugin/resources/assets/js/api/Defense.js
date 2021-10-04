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

    static deleteStudentRegistration(charonId, studentId, labId, submissionId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + `/registration?user_id=${studentId}&defLab_id=${labId}&submission_id=${submissionId}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting student registration.\n' + error, 'danger')
        })
    }

    static deferStudentRegistration(labId, regId, userId, charonId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/registration/defer?user_id=${userId}&defLab_id=${labId}&reg_id=${regId}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deferring student registration.\n' + error, 'danger');
        })
    }

    static getDefenseData(charon_id, student_id, then) {
        axios.get(`/mod/charon/api/charons/${charon_id}/registrations?id=${charon_id}&user_id=${student_id}`)
            .then(result => {
                then(result.data);
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error getting defense data.\n' + error, 'danger');
        })
    }
}

export default Defense
