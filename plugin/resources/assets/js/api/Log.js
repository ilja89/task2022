class Log {

    static userHasLoggingEnabled(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/queryLoggingEnabled')
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error checking if user has logging enabled.\n' + error, 'danger')
        })
    }

    static enableLogging(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/enableLogging')
            .then(then).catch(error => {
                VueEvent.$emit('show-notification', 'Error enabling logging for the user. \n' + error, 'danger')
        })
    }

    static disableLogging(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/disableLogging')
            .then(then).catch(error => {
            VueEvent.$emit('show-notification', 'Error disabling logging for the user. \n' + error, 'danger')
        })
    }

    static findUsersWithLoggingEnabled(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/findUsersWithLoggingEnabled')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error fetching users with logging enabled.\n' + error, 'danger')
        })
    }
}

export default Log