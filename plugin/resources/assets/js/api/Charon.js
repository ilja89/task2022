class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static all(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/charons')
            .then(response => {
                then(response.data)
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

    static saveCharon(charon, defense_start_time, defense_deadline, defense_labs, then) {
        axios.post(Charon.getRoot() + '/charons/' + charon.id, {
            defense_deadline: defense_deadline,
            defense_start_time: defense_start_time,
            defense_duration: charon.defense_duration === 0 ? 5 : charon.defense_duration,
            defense_labs: defense_labs,
            choose_teacher: charon.choose_teacher,
            defense_threshold: charon.defense_threshold,
            system_extra: charon.system_extra === "" ? null : charon.system_extra,
            tester_extra: charon.tester_extra === "" ? null : charon.tester_extra,
            tester_type_code: charon.tester_type_code,
            docker_timeout: charon.docker_timeout,
            docker_content_root: charon.docker_content_root === "" ? null : charon.docker_test_root,
            docker_test_root: charon.docker_test_root === "" ? null : charon.docker_test_root,
            group_size: charon.group_size
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving Charon.', 'danger')
        })
    }
}

export default Charon
