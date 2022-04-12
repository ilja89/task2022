class User {

    static getStudentInfo(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId)
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving user.\n' + error, 'danger')
            })
    }

    static getReportTable(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/report-table')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving report table.\n' + error, 'danger')
            })
    }

    static findActiveUsers(courseId, period, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/active', { params: { period } })
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving active students.\n' + error, 'danger')
            })
    }

    static getStudentsDistribution(courseId, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/distribution')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving distribution of students.\n' + error, 'danger')
            })
    }

    static getTeachersInLab(courseId, labId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teachers.\n' + error, 'danger')
        })
    }

    static getAllEnrolled(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/allEnrolled')
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving all enrolled users.\n' + error, 'danger')
        })
    }

    static getPossiblePointsForCourse(courseId, studentId, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/users/${studentId}/possible-points`)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teachers.\n' + error, 'danger')
        })
    }

    static getUserCharonsDetails(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/charons-details')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving user charons details.\n' + error, 'danger')
            })
    }

    static getStudentsInCourse(courseId, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/allStudents')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving all users for course.\n' + error, 'danger')
        })
    }
}

export default User
