module.exports = {
    methods: {

        getComments(charonId, studentId) {
            return new Promise((resolve, reject) => {
                Api.get('/mod/charon/api/charons/' + charonId + '/comments', {student_id: studentId})
                    .then(response => resolve(response))
                    .catch(error => reject(error));
            });
        },

        updateSubmissionResults(charonId, submission) {
            return new Promise((resolve, reject) => {
                Api.post('/mod/charon/api/charons/' + charonId + '/submissions/' + submission.id, { submission: submission })
                    .then(response => resolve(response))
                    .catch(error => reject(error));
            });
        },

        saveCharonComment(charonId, studentId, comment) {
            return new Promise((resolve, reject) => {
                Api.post('/mod/charon/api/charons/' + charonId + '/comments', {
                    comment: comment,
                    student_id: studentId
                })
                    .then(response => resolve(response))
                    .catch(error => reject(error));
            });
        }
    }
};
