import {Error} from "./index";

class File {

    static findBySubmission(submissionId, then) {
        axios.get('/mod/charon/api/submissions/' + submissionId + '/files')
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving files.\n')
            })
    }
}

export default File
