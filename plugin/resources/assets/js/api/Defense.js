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

    static deleteStudentRegistration(registration_id, student_id, lab_id, submission_id, charon_id, then) {
        axios.put('/mod/charon/api/charons/' + charon_id + `/registration`, {
            registration_id: registration_id,
            charon_id: charon_id,
            lab_id: lab_id,
            student_id: student_id,
            submission_id: submission_id
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting student registration.\n' + error, 'danger')
            /*axios.put('/mod/charon/api/charons/' + registration_id + `/registration?user_id=${student_id}&defLab_id=${lab_id}&submission_id=${submission_id}`)
                .then(response => {
                    then(response.data)
                }).catch(error => {
                VueEvent.$emit('show-notification', 'Error deleting student registration.\n' + error, 'danger')
            })*/
        })
    }

}

export default Defense