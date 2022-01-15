import {Error} from "./index";

class Teacher {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getAllTeachers(courseId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving teachers.\n')
        })
    }

    static getReport(courseId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teachers/report')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving teacher report.\n')
        })
    }

    static getByLab(charonId, labId, then) {
        axios.get(Teacher.getRoot() + '/charons/' + charonId + '/labs/' + labId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving lab teachers.\n')
        })
    }

    static getByTeacher(courseId, teacherId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teacher/' + teacherId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving teacher.\n')
        })
    }

    static getTeacherAggregatedData(courseId, teacherId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teacher/' + teacherId + '/aggregated')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving teacher aggregated data.\n')
        })
    }

    static update(courseId, labId, teacherId, body, then) {
        axios.put(Teacher.getRoot() + '/course/' + courseId + '/labs/' + labId + '/teachers/' + teacherId, body)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error updating teacher.\n')
        })
    }

}
export default Teacher
