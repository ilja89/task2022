import {Error} from "./index";

class Course {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getCourseObject(courseId, then) {
        window.axios.get(Course.getRoot() + '/courses/' + courseId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving course.\n')
            })
    }

    static getTesterTypes(courseId, then) {
        window.axios.get(Course.getRoot() + '/courses/' + courseId + '/testerTypes/all')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving tester types.\n')
            })
    }

    static addTesterType(courseId, name, then) {
        window.axios.post(Course.getRoot() + '/courses/' + courseId + '/testerTypes/add/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error adding a tester type.\n')
            })
    }

    static removeTesterType(courseId, name, then) {
        window.axios.delete(Course.getRoot() + '/courses/' + courseId + '/testerTypes/remove/' + name)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error removing a tester type.\n')
            })
    }

}

export default Course
