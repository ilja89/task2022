class Output {

    static findBySubmission(submissionId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/submissions/' + submissionId + '/outputs')
            .then(({data}) => {
                then(data);
                VueEvent.$emit('hide-loader');
            });
    }
}

export default Output;
