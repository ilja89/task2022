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

    static registerByTeacher(charonId, studentId, defenseLabId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/registerDefense?user_id=${studentId}`, {
            charon_id: charonId,
            defense_lab_id: defenseLabId,
        }).then((response) => {
            then(response.data)
        }).catch(error => {
            if (error.response && error.response.data && error.response.data.title) {
                VueEvent.$emit('show-notification', error.response.data.title + ' ' + error.response.data.detail, 'danger')
            } else {
                console.error(error);
                VueEvent.$emit('show-notification', 'Error creating a new defense registration.\n' + error, 'danger')
            }
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

    static deleteStudentRegistration(charon_id, studentId, defense_lab_id, submission_id, then) {
        axios.delete('/mod/charon/api/charons/' + charon_id + `/registration?user_id=${studentId}&defLab_id=${defense_lab_id}&submission_id=${submission_id}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting student registration.\n' + error, 'danger')
        })
    }

}

export default Defense