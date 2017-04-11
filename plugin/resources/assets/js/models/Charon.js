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
}

export default Charon
