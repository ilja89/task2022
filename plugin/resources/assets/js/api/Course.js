class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getCourseObject(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving course.', 'danger')
        })
    }

    static getTesterTypes(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/testerTypes/all')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving tester types.', 'danger')
        })
    }

    static addTesterType(courseId, name, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/testerTypes/add/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error adding a tester type.', 'danger')
        })
    }

    static removeTesterType(courseId, name, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/testerTypes/remove/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error removing a tester type.', 'danger')
        })
    }

}

export default Charon
