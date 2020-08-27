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

    static saveDefenseProgress(courseId, defenseId, progress, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/defenders/' + defenseId, {
            progress: progress
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving defense progress.', 'danger')
        })
    }

}

export default Defense