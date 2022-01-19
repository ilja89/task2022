import CharonFormat from "../helpers/CharonFormat";
import {Error} from "./index";

class Lab {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs')
            .then(response => {
                let labs = response.data
                CharonFormat.getNamesForLabs(labs)
                then(labs)
            }).catch(error => {
                Error.throw(error, 'Error retrieving labs.\n')
            })
    }

    static save(courseId, start, end, name, teachers, charons, groups, weeks, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs', {
            start: start,
            end: end,
            name: name,
            charons: charons,
            teachers: teachers,
            groups: groups,
            weeks: weeks
        }).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error saving lab.\n')
        })
    }

    static delete(courseId, labId, then) {
        axios.delete('/mod/charon/api/courses/' + courseId + '/labs/' + labId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            Error.throw(error, 'Error deleting lab.\n')
        })
    }

    static update(courseId, labId, start, end, name, teachers, charons, groups, then) {
        axios.post('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/update', {
            start: start,
            end: end,
            name: name,
            charons: charons,
            groups: groups,
            teachers: teachers
        }).then(response => {
            then(response)
        }).catch(error => {
            Error.throw(error, 'Error updating lab.\n')
        })
    }

    static getByCharonId(charonId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving labs for Charon.\n')
            })
    }

    static getByLabId(labId, then) {
        axios.get('/mod/charon/api/charons/' + labId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving labs.\n')
            })
    }

    static checkRegistrations(courseId, labId, filters, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/registrations', {params: filters})
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error getting registrations.\n')
            })
    }

    static getGroups(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/groups')
            .then(response => {
                then(response.data);
            }).catch(error => {
                Error.throw(error, 'Error retrieving groups.\n')
            });
    }

    static getLabQueueStatus(charonId, defLabId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/defenseLab/' + defLabId + '/queueStatus?user_id=' + studentId)
            .then(response => {
                then(response.data);
            }).catch(error => {
                Error.throw(error, 'Error retrieving queue status.\n')
            });
    }
}

export default Lab
