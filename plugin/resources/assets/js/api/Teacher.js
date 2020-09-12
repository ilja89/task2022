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
            VueEvent.$emit('show-notification', 'Error retrieving teachers.', 'danger')
        })
    }

    static getReport(courseId, then) {
        axios.get(Teacher.getRoot() + '/courses/' + courseId + '/teachers/report')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving teacher report.', 'danger')
        })
    }

    static getByLab(charonId, labId, then) {
        axios.get(Teacher.getRoot() + '/charons/' + charonId + '/labs/' + labId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.', 'danger')
        })
    }

    static update(charonId, labId, teacherId, body, then) {
        axios.put(Teacher.getRoot() + '/charons/' + charonId + '/labs/' + labId + '/teachers/' + teacherId, body)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating teacher.', 'danger')
        })
    }

}
export default Teacher
