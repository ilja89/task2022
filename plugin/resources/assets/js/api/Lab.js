class Lab {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }

    static save(courseId, start, end, teachers, charons, weeks, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs', {
            start: start,
            end: end,
            charons: charons,
            teachers: teachers,
            weeks: weeks
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving lab.', 'danger')
        })
    }

    static delete(courseId, labId, then) {
        axios.delete('/mod/charon/api/courses/' + courseId + '/labs/' + labId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting lab.', 'danger')
        })
    }

    static update(courseId, labId, start, end, teachers, charons, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/update', {
            start: start,
            end: end,
            charons: charons,
            teachers: teachers
        }).then(response => {
            then(response)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating lab.', 'danger')
        })
    }

    static getByCharonId(charonId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs for Charon.', 'danger')
        })
    }

    static getByLabId(labId, then) {
        axios.get('/mod/charon/api/charons/' + labId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }
}

export default Lab
