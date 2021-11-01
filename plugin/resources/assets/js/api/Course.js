class Course {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getCourseObject(courseId, then) {
        window.axios.get(Course.getRoot() + '/courses/' + courseId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving course.\n' + error, 'danger')
        })
    }

    static getCourseStudentCount(courseId, then) {
        window.axios.get(Course.getRoot() + '/courses/' + courseId + '/students/count')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving students.\n' + error, 'danger')
        })
    }

    static getTesterTypes(courseId, then) {
        window.axios.get(Course.getRoot() + '/courses/' + courseId + '/testerTypes/all')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving tester types.\n' + error, 'danger')
        })
    }

    static addTesterType(courseId, name, then) {
        window.axios.post(Course.getRoot() + '/courses/' + courseId + '/testerTypes/add/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error adding a tester type.\n' + error, 'danger')
        })
    }

    static removeTesterType(courseId, name, then) {
        window.axios.delete(Course.getRoot() + '/courses/' + courseId + '/testerTypes/remove/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error removing a tester type.\n' + error, 'danger')
        })
    }

}

export default Course
