import {Error} from "./index";

class User {

    static allStudents(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/students/search')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving students with course id.\n')
            })
    }

    static getStudentInfo(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId)
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving user.\n')
            })
    }

    static getReportTable(courseId, userId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/users/' + userId + '/report-table')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving report table.\n')
            })
    }


    static findActiveUsers(courseId, period, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/active', { params: { period } })
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving active students.\n')
            })
    }

    static getStudentsDistribution(courseId, then) {
        window.axiosNoLoading.get('/mod/charon/api/courses/' + courseId + '/users/distribution')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving distribution of students.\n')
            })
    }

    static getTeachersInLab(courseId, labId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/teachers')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving teachers.\n')
        })
    }
}

export default User
