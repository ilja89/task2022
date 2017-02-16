module.exports = {
    methods: {

        updateSubmissionResults(charonId, submission) {
            return new Promise((resolve, reject) => {
                Api.post('/mod/charon/api/charons/' + charonId + '/submissions/' + submission.id, { submission: submission })
                    .then(response => resolve(response))
                    .catch(error => reject(error));
            });
        },
    }
};
