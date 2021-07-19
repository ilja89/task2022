import CharonFormat from "../helpers/CharonFormat";

class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static all(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/charons')
            .then(response => {
                then(CharonFormat.formatCharons(response.data))
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving Charons.\n' + error, 'danger')
        })
    }

    static deleteById(charonId, then) {
        window.axios.delete(Charon.getRoot() + '/charons/' + charonId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting a Charon.\n' + error, 'danger')
        })
    }

    static fetchLatestLogs(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/logs')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error fetching logs.\n' + error, 'danger')
        })
    }

    static getResultForStudent(charonId, userId, then) {
        window.axios.get(Charon.getRoot() + '/charons/' + charonId + '/results/' + userId)
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving results.\n' + error, 'danger')
        })
    }

    static saveCharon(charon, then) {
        axios.post(Charon.getRoot() + '/charons/' + charon.id, charon).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving Charon.\n' + error, 'danger')
        })
    }

    static retestSubmissions(charonId, then) {
        window.axios.get(Charon.getRoot() + '/charons/' + charonId + '/retest').then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving results.\n' + error, 'danger')
        });
    }

    static getCharonVersionDate(then) {
        console.log("CHARON");
        window.axios.get(Charon.getRoot() + '/charons/releasedate').then(response => {
            then(response.data);
        })
    }
}

export default Charon
