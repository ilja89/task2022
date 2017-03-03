class File {

    static findBySubmission(submissionId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/submissions/' + submissionId + '/files')
            .then(({data}) => {
                then(data);
                VueEvent.$emit('hide-loader');
            });
    }
}

export default File;
