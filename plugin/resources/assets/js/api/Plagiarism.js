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

    static fetchMatches(charonId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/matches`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching plagiarism matches.\n' + error, 'danger')
            })
    }

    static fetchMatchesByRun(runId, charonId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/run-matches?run_id=` + runId)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching plagiarism matches.\n' + error, 'danger')
            })
    }

    static runPlagiarismCheck(charonId, then) {
        axios.post(`${this.getRoot()}/charons/${charonId}/plagiarism/run`)
            .then(response => {
                then(response)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error running plagiarism check.\n' + error, 'danger')
            })
    }

    static getLatestCheckStatus(charonId, runId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/plagiarism-checks?run_id=${runId}`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error getting status of latest plagiarism check.\n' + error, 'danger')
            })
    }

    static getCheckHistory(courseId, then) {
        axios.get(`${this.getRoot()}/courses/${courseId}/checks-history/`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error getting checks history.\n' + error, 'danger')
            })
    }

    static updateMatchStatus(courseId, matchId, newStatus, comment, then) {
        axios.post(`${this.getRoot()}/courses/${courseId}/updateMatchStatus`, {
            matchId: matchId,
            newStatus: newStatus,
            comment: comment,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating match.\n' + error, 'danger')
        })
    }

    static fetchStudentActiveMatches(courseId, username, then) {
        axios.get(`${this.getRoot()}/courses/${courseId}/users/${username}/activeMatches`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching user plagiarism active matches.\n' + error, 'danger')
            })
    }

    static fetchStudentInactiveMatches(courseId, username, then) {
        axios.get(`${this.getRoot()}/courses/${courseId}/users/${username}/inactiveMatches`)
            .then(response => {
                then(response.data)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching user plagiarism inactive matches.\n' + error, 'danger')
            })
    }
}

export default Plagiarism
