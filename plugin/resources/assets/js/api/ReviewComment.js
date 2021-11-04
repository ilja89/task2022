class ReviewComment {

    static add(reviewComment, submissionFileId, charonId, notify, submissionId, filePath, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/add', {
            submission_file_id: submissionFileId,
            review_comment: reviewComment,
            notify: notify,
            submission_id: submissionId,
            file_path: filePath
        }).then(response => {
            then(response.data)
        }).catch(error => {
            this.throwError(error, 'Error adding review comment.\n');
        })

    }

    static delete(reviewCommentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/reviewComments/' + reviewCommentId + '/delete'
        ).then(response => {
            then(response.data)
        }).catch(error => {
            this.throwError(error, 'Error deleting review comment.\n');
        });
    }

    static clearNotifications(reviewCommentIds, charonId, studentId, then) {
        axios.put('/mod/charon/api/charons/' + charonId + '/reviewComments/clear?user_id=' + studentId, {
            reviewCommentIds: reviewCommentIds,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            this.throwError(error, 'Error clearing review comments\' notifications.\n');
        });
    }

    static throwError(error, errorText) {
        VueEvent.$emit('show-notification',
            error.response && error.response.data && error.response.data.title
                ? error.response.data.title + ' ' + error.response.data.detail
                : errorText + error, 'danger')
    }
}

export default ReviewComment
