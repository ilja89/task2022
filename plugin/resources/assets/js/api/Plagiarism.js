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
                if (error.response && error.response.data && error.response.data.message) {
                    VueEvent.$emit('show-notification', error.response.data.message, 'danger')
                } else {
                    VueEvent.$emit('show-notification', 'Error running plagiarism checksuite.', 'danger')
                }
            })
    }

    static fetchSimilarities(charonId, then) {
        axios.get(`${this.getRoot()}/charons/${charonId}/similarities`)
            .then(response => {
                console.log(response)
                then(response)
            })
            .catch(error => {
                VueEvent.$emit('show-notification', 'Error fetching plagiarism similarities.', 'danger')
            })
    }
}

export default Plagiarism
