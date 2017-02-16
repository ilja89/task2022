class Comment {

    static all(charonId, studentId, then) {
        VueEvent.$emit('show-loader');
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId } })
            .then(response => {
                then(response.data);
                VueEvent.$emit('hide-loader');
            });
    }

    static save(comment, charonId, studentId, then) {
        VueEvent.$emit('show-loader');
        axios.post('/mod/charon/api/charons/' + charonId + '/comments', {
            comment: comment,
            student_id: studentId
        })
            .then(response => {
                then(response.data.comment);
                VueEvent.$emit('hide-loader');
            });
    }
}

export default Comment;
