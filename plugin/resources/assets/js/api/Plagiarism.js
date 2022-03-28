import axios from 'axios'

class Plagiarism {
    static getRoot() {
        return '/mod/charon/api'
    }

    static runPlagiarism(charonId, then) {
        axios.post(`${this.getRoot()}/charons/${charonId}/checksuite/run`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error running plagiarism checksuite.\n' + error, 'danger')
            })
    }

    static fetchSimilarities(charonId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/similarities`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching plagiarism similarities.\n' + error, 'danger')
            })
    }

    static fetchMatches(courseId, charonId, then) {
        axios.get(`${this.getRoot()}/courses/${courseId}/charons/${charonId}/matches`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching plagiarism matches.\n' + error, 'danger')
            })
    }

    static updateMatchStatus(courseId, matchId, newStatus, then) {
        axios.post(`${this.getRoot()}/courses/${courseId}/updateMatchStatus`, {
            matchId: matchId,
            newStatus: newStatus
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating match.\n' + error, 'danger')
        })
    }
}

export default Plagiarism
