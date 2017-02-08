module.exports = {
    methods: {

        getComments(charon_id, student_id, vuePopup) {
            axios.get('/mod/charon/api/charons/' + charon_id + '/comments', {
                params: {
                    student_id: student_id
                }
            })
                .then((response) => {
                    vuePopup.context.active_comments = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        getCharonsForCourse(courseId) {
            return new Promise((resolve, reject) => {
                Api.get('/mod/charon/api/courses/' + courseId + '/charons')
                    .then(response => resolve(response))
                    .catch(error => reject(error));
            });
        },

        getSubmissionsForUser(charonId, userId) {
            return new Promise((resolve, reject) => {
                Api.get('/mod/charon/api/charons/' + charonId + '/submissions', { user_id: userId })
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
