class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
            console.log(error)
            VueEvent.$emit('show-notification', 'Error retrieving defense registrations.', 'danger')
        })
    }

    static filtered(courseId, after, before, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/' + after + '/' + before)
            .then(response => {
                then(response.data)
            }).catch(error => {
                console.log(error)
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

}

export default Defense