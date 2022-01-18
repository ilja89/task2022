import {Error} from "./index";

class Comment {

    static all(charonId, studentId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/comments', { params: { student_id: studentId } })
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving comments.\n')
            })
    }

    static save(comment, charonId, studentId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/comments', {
            comment: comment,
            student_id: studentId
        }).then(response => {
            then(response.data.comment)
        }).catch(error => {
            Error.throw(error, 'Error saving comment.\n')
        })
    }
}

export default Comment
