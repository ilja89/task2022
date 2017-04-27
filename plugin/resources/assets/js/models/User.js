class User {

    static findById(courseId, userId, then) {
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
}

export default User
