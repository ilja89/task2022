class Comment {

    static all(charonId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId } })
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving comments.', 'danger')
            })
    }

    static save(comment, charonId, studentId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/comments', {
            comment: comment,
            student_id: studentId
        }).then(response => {
            then(response.data.comment)
        }).catch(error => {
            VueEvent.$emit('show-notification', 'Error saving comment.', 'danger')
        })
    }
}

export default Comment
