class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static all(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/charons')
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving Charons.', 'danger')
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

    static saveCharonDefenseStuff(charonId, defense_deadline, defense_duration, defense_labs, choose_teacher, defense_threshold, then) {
        axios.post(Charon.getRoot() + '/charons/' + charonId, {
            defense_deadline: defense_deadline,
            defense_duration: defense_duration,
            defense_labs: defense_labs,
            choose_teacher: choose_teacher,
            defense_threshold: defense_threshold
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving Charon defending stuff.', 'danger')
        })
    }
}

export default Charon
