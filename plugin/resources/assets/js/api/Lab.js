class Lab {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }

    static save(courseId, start, end, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs', {
            start: start,
            end: end
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving lab.', 'danger')
        })
        //Lab.save(this.course.id, start, end, lab => {
        //                     this.labs.push(lab)
        //                     VueEvent.$emit('show-notification', 'Lab saved!')
        //                 });
    }
}

export default Lab
