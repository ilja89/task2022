class SubmissionComment {

    /*static all(charonId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId } })
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving comments.\n' + error, 'danger')
        })
    }*/

    static save(comment, submissionId, then) {
        axios.post('/mod/charon/api/charons/savecomment', {
            comment: comment,
            submissionId: submissionId
        }).then(response => {
            then(response.data.comment)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving comment.\n' + error, 'danger')
        })
    }
}

export default SubmissionComment