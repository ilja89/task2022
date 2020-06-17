class User {

    static getStudentInfo(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId)
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving user.', 'danger')
            })
    }

    static getReportTable(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/report-table')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving report table.', 'danger')
            })
    }


    static findActiveUsers(courseId, period, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/active', { params: { period } })
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving active students.', 'danger')
            })
    }

    static getStudentsDistribution(courseId, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/distribution')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving distribution of students.', 'danger')
            })
    }
}

export default User
