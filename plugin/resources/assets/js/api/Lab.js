class Lab {

    static all(then) {
        axios.get('/mod/charon/api/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }

    static save(start, end, then) {
        axios.post('/mod/charon/api/labs/', {
            start: start,
            end: end
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving lab.', 'danger')
        })
    }
}

export default Lab
