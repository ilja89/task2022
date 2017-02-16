class Comment {

    static all(charonId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId } })
            .then(response => then(response.data));
    }

    static save(comment, charonId, studentId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/comments', {
            comment: comment,
            student_id: studentId
        })
            .then(response => then(response.data.comment));
    }
}

export default Comment;
