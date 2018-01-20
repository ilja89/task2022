class Submission {

    static findByUserCharon(userId, charonId, then) {
        axios.get(`/mod/charon/api/charons/${charonId}/submissions`, { params: { user_id: userId } })
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
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/${submission.id}`, { submission: submission })
            .then(response => {
                console.log('answer')
                console.log(response)
                then(response.data)
            }).catch(error => {
                console.log('error')
                console.log(error)
                console.log(error.response)
                VueEvent.$emit('show-notification', 'Error updating submission.', 'danger')
            })
    }

    static findById(submissionId, then) {
        axios.get(`/mod/charon/api/submissions/${submissionId}`)
            .then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving submission.', 'danger')
            })
    }

    static addNewEmpty(charonId, studentId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/add`, { student_id: studentId })
            .then(response => {
                then(response)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error adding new submission.', 'danger')
            })
    }

    static canLoadMore() {
        return this.nextUrl !== null
    }

    static retest(submissionId, then) {
        window.axios.post(`/mod/charon/api/submissions/${submissionId}/retest`)
            .then(response => {
                if (response.data.status === 200) {
                    then(response)
                } else {
                    window.VueEvent.$emit('show-notification', response.data.data.detail, 'danger')
                }
            })
            .catch(error => {
                window.VueEvent.$emit('show-notification', 'Error retesting.', 'danger')
            })
    }

    static findLatest(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/latest`)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving latest submissions.', 'danger')
            })
    }

    static findSubmissionCounts(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/counts`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
                VueEvent.$emit('show-notification', 'Error retrieving Submission submission counts.', 'danger')
            })
    }
}

Submission.nextUrl = null

export default Submission
