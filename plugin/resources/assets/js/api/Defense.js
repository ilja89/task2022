class Defense {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving defense registrations.', 'danger')
        })
    }

    static filtered(courseId, after, before, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/defenseRegistrations/' + after + '/' + before)
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving filtered defense registrations.', 'danger')
        })
    }

}

export default Defense