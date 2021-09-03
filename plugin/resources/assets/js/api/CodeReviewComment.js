class CodeReviewComment {

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

    static delete(commentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/codeReviewComments/' + commentId + '/delete')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting lab.\n' + error, 'danger')
        });
    }
}

export default CodeReviewComment