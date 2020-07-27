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

}

export default Charon
