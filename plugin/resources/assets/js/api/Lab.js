import CharonFormat from "../helpers/CharonFormat";

class Lab {

    static all(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs')
            .then(response => {
                let labs = response.data
                CharonFormat.getNamesForLabs(labs)
                then(labs)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.\n' + error, 'danger')
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
            VueEvent.$emit('show-notification', 'Error saving lab.\n' + error, 'danger')
        })
    }

    static delete(courseId, labId, then) {
        axios.delete('/mod/charon/api/courses/' + courseId + '/labs/' + labId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting lab.\n' + error, 'danger')
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
            VueEvent.$emit('show-notification', 'Error updating lab.\n' + error, 'danger')
        })
    }

    static getByCharonId(charonId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs for Charon.\n' + error, 'danger')
        })
    }

    static getByLabId(labId, then) {
        axios.get('/mod/charon/api/charons/' + labId + '/labs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving labs.\n' + error, 'danger')
        })
    }

    static checkRegistrations(courseId, labId, filters, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/labs/' + labId + '/registrations', {params: filters})
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error getting registrations.\n' + error, 'danger')
        })
    }

    static getGroups(courseId, then) {
        axios.get('/mod/charon/api/courses/' + courseId + '/groups')
            .then(response => {
                then(response.data);
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving groups.\n' + error, 'danger');
        });
    }
}

export default Lab
