class Lab {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }

    static save(courseId, start, end, teachers, weeks, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs', {
            start: start,
            end: end,
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

    static update(courseId, labId, start, end, teachers, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/update', {
            start: start,
            end: end,
            teachers: teachers
        }).then(response => {
            then(response)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating lab.', 'danger')
        })
    }
}
export default Lab
