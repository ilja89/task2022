class Charon {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/charons')
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving Charons.', 'danger')
            })
    }

    static getResultForStudent(charonId, userId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/results/' + userId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving results.', 'danger')
            })
    }

    static retest(charonId, userId, then) {
        window.axios.post(`/mod/charon/api/charons/${charonId}/retest`, {
            userId: userId,
        })
            .then(response => {
                then(response)
            })
            .catch(error => {
                window.VueEvent.$emit('show-notification', 'Retesting is not supported yet!', 'danger')
            })
    }
}

export default Charon
