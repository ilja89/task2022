class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving defense registrations.', 'danger')
        })
    }

    static filtered(courseId, after, before, teacher_id, progress, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/' + after + '/' + before + '/' + teacher_id + '/' + progress)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving filtered defense registrations.', 'danger')
        })
    }

    static getTeacherForStudent(courseId, studentId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/student/' + studentId + '/teacher')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving student teacher.', 'danger')
        })
    }

    static updateRegistration(courseId, defenseId, progress, teacher_id, then) {
        axios.put('/mod/charon/api/courses/' + courseId + '/registration/' + defenseId, {
            progress: progress,
            teacher_id: teacher_id
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving defense progress.', 'danger')
        })
    }

    static deleteStudentRegistration(charon_id, studentId, defense_lab_id, submission_id, then) {
        axios.delete('/mod/charon/api/charons/' + charon_id + `/registration?student_id=${studentId}&defLab_id=${defense_lab_id}&submission_id=${submission_id}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting student registration.', 'danger')
        })
    }

}

export default Defense