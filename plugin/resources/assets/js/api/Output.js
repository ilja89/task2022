class Output {

    static findBySubmission(submissionId, then) {
        axios.get('/mod/charon/api/submissions/' + submissionId + '/outputs')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving outputs.', 'danger')
            })
    }
}

export default Output
