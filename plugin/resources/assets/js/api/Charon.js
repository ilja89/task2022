class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static all(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/charons')
            .then(response => {
                then(CharonFormat.formatCharons(response.data))
            }).catch(error => {
            console.log(error)
            VueEvent.$emit('show-notification', 'Error retrieving Charons.', 'danger')
        })
    }

    static deleteById(charonId, then) {
        window.axios.delete(Charon.getRoot() + '/charons/' + charonId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting a Charon.', 'danger')
        })
    }

    static fetchLatestLogs(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/logs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error fetching logs.', 'danger')
        })
    }

    static getResultForStudent(charonId, userId, then) {
        window.axios.get(Charon.getRoot() + '/charons/' + charonId + '/results/' + userId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving results.', 'danger')
        })
    }

    static saveCharon(charon, then) {
        axios.post(Charon.getRoot() + '/charons/' + charon.id, charon).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving Charon.', 'danger')
        })
    }
}

export default Charon
