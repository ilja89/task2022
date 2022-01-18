import CharonFormat from "../helpers/CharonFormat";
import {Error} from "./index";

class Charon {

    static getRoot() {
        return '/mod/charon/api'
    }

    static getTemplates(charonId, then) {
        return axios.get(Charon.getRoot() + `/charons/${charonId}/templates`)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error getting templates.\n')
            })
    }

    static all(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/charons')
            .then(response => {
                then(CharonFormat.formatCharons(response.data))
            }).catch(error => {
                Error.throw(error, 'Error retrieving Charons.\n')
            })
    }

    static getTesterLanguage(testerTypeCode, courseId) {
        return window.axios.get(Charon.getRoot() + '/courses/'+ courseId +'/testerType/' + testerTypeCode)
            .then(response => {
                return response.data.testerType;
            }).catch(error  => {
                Error.throw(error, 'Error getting editor language.\n')
        })

    }

    static deleteById(charonId, then) {
        window.axios.delete(Charon.getRoot() + '/charons/' + charonId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error deleting a Charon.\n')
            })
    }

    static fetchLatestLogs(courseId, then) {
        window.axios.get(Charon.getRoot() + '/courses/' + courseId + '/logs').then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error fetching logs.\n')
            })
    }

    static getResultForStudent(charonId, userId, then) {
        window.axios.get(Charon.getRoot() + '/charons/' + charonId + '/results/' + userId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving results.\n')
            })
    }

    static saveCharon(charon, then) {
        axios.post(Charon.getRoot() + '/charons/' + charon.id, charon).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error saving Charon.\n')
        })
    }

    static retestSubmissions(charonId, then) {
        window.axios.get(Charon.getRoot() + '/charons/' + charonId + '/retest').then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error retrieving results.\n')
        });
    }
}

export default Charon
