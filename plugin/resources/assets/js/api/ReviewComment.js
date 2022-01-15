import {Error} from "./index";

class ReviewComment {

    static add(reviewComment, submissionFileId, charonId, notify, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/reviewComments/add', {
            submission_file_id: submissionFileId,
            review_comment: reviewComment,
            notify: notify
        }).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throwWithCheck(error, 'Error adding review comment.\n');
        })

    }

    static delete(reviewCommentId, charonId, then) {
        axios.delete('/mod/charon/api/charons/' + charonId + '/reviewComments/' + reviewCommentId + '/delete'
        ).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throwWithCheck(error, 'Error deleting review comment.\n');
        });
    }

    static clearNotifications(reviewCommentIds, charonId, studentId, then) {
        axios.put('/mod/charon/api/charons/' + charonId + '/reviewComments/clear?user_id=' + studentId, {
            reviewCommentIds: reviewCommentIds,
        }).then(response => {
            then(response.data)
        }).catch(error => {
            Error.throwWithCheck(error, 'Error clearing review comments\' notifications.\n');
        });
    }
}

export default ReviewComment
