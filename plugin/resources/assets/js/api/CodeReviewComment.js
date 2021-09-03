class CodeReviewComment {

    static all(charonId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId }
        }).then(response => {
                then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving comments.\n' + error, 'danger')
        })
    }

    static save(comment, submissionFileId, charonId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/save', {
            submission_file_id: submissionFileId,
            comment: comment
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving comment.\n' + error, 'danger')
        })
    }
}

export default CodeReviewComment