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
}

export default Plagiarism
