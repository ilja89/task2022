class ReviewComment {

    static save(reviewComment, submissionFileId, charonId, notify, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/save', {
            submission_file_id: submissionFileId,
            review_comment: reviewComment,
            notify: notify,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving review comment.\n' + error, 'danger')
        })
    }

    static delete(reviewCommentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/reviewComments/' + reviewCommentId + '/delete')
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error deleting review comment.\n' + error, 'danger')
        });
    }

    static clearNotifications(reviewCommentIds, charonId, studentId, then) {
        axios.put('/mod/charon/api/charons/' + charonId + '/reviewComments/clear?user_id=' + studentId, {
            reviewCommentIds: reviewCommentIds,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error clearing code review comments\' notifications.\n'
                + error, 'danger');
        });
    }
}

export default ReviewComment