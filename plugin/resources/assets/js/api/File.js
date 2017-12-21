class File {

    static findBySubmission(submissionId, then) {
        axios.get('/mod/charon/api/submissions/' + submissionId + '/files')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving files.', 'danger')
            })
    }
}

export default File
