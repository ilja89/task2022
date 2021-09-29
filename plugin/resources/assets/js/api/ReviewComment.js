class ReviewComment {

    static add(reviewComment, submissionFileId, charonId, notify, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/add', {
            submission_file_id: submissionFileId,
            review_comment: reviewComment,
            notify: notify,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification',
                error.response && error.response.data && error.response.data.title
                    ? error.response.data.title + ' ' + error.response.data.detail
                    : 'Error adding review comment.\n' + error, 'danger')
        })

    }

    static delete(reviewCommentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/reviewComments/' + reviewCommentId + '/delete'
        ).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification',
                error.response && error.response.data && error.response.data.title
                    ? error.response.data.title + ' ' + error.response.data.detail
                    : 'Error deleting review comment.\n' + error, 'danger')
        });
    }

    static clearNotifications(reviewCommentIds, charonId, studentId, then) {
        axios.put('/mod/charon/api/charons/' + charonId + '/reviewComments/clear?user_id=' + studentId, {
            reviewCommentIds: reviewCommentIds,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            VueEvent.$emit('show-notification',
                error.response && error.response.data && error.response.data.title
                    ? error.response.data.title + ' ' + error.response.data.detail
                    : 'Error clearing review comments\' notifications.\n' + error, 'danger')
        });
    }
}

export default ReviewComment