class CodeReviewComment {

    static save(reviewComment, submissionFileId, charonId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/save', {
            submission_file_id: submissionFileId,
            review_comment: reviewComment
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving code review comment.\n' + error, 'danger')
        })
    }

    static delete(reviewCommentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/codeReviewComments/' + reviewCommentId + '/delete')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting code review comment.\n' + error, 'danger')
        });
    }
}

export default CodeReviewComment