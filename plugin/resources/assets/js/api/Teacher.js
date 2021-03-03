import Submission from "./Submission";

class Teacher {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getAllTeachers(courseId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teachers.\n' + error, 'danger')
        })
    }

    static getReport(courseId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teachers/report')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teacher report.\n' + error, 'danger')
        })
    }

    static getByLab(charonId, labId, then) {
        axios.get(Teacher.getRoot() + '/charons/' + charonId + '/labs/' + labId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving lab teachers.\n' + error, 'danger')
        })
    }

    static getByTeacher(courseId, teacherId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teacher/' + teacherId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teacher.\n' + error, 'danger')
        })
    }

    static getTeacherAggregatedData(courseId, teacherId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teacher/' + teacherId + '/aggregated')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teacher aggregated data.\n' + error, 'danger')
        })
    }

    static update(courseId, labId, teacherId, body, then) {
        axios.put(Teacher.getRoot() + '/course/' + courseId + '/labs/' + labId + '/teachers/' + teacherId, body)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating teacher.\n' + error, 'danger')
        })
    }

}
export default Teacher
