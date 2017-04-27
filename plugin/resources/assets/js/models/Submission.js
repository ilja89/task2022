class Submission {

    static findByUserCharon(userId, charonId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/submissions', { params: { user_id: userId } })
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving submissions.', 'danger')
            })
    }

    static getNext(then) {
        axios.get(Submission.nextUrl)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving submissions.', 'danger')
            })
    }

    static update(charonId, submission, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/submissions/' + submission.id, { submission: submission })
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error updating submission.', 'danger')
            })
    }

    static findById(charonId, submissionId, then) {
        axios.get('/mod/charon/api/charons/' + charonId + '/submissions/' + submissionId)
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving submission.', 'danger')
            })
    }

    static addNewEmpty(charonId, studentId, then) {
        axios.post('/mod/charon/api/charons/' + charonId + '/submissions/add', { student_id: studentId })
            .then(response => {
                then(response)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error adding new submission.', 'danger')
            })
    }

    static canLoadMore() {
        return this.nextUrl !== null
    }
}
Submission.nextUrl = null

export default Submission
