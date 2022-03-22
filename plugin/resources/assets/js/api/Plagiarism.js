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

    static runPlagiarismCheck(courseId, charonId, then) {
        axios.post(`${this.getRoot()}/courses/${courseId}/charons/${charonId}/plagiarism/run`)
            .then(response => {
                then(response)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error running plagiarism check.\n' + error, 'danger')
            })
    }

    static getLatestCheckStatus(charonId, checkId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/plagiarism-checks/${checkId}`)
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
}

export default Plagiarism
