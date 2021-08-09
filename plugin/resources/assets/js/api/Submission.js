class Submission {

    static findByUserCharon(userId, charonId, then) {
        axios.get(`/mod/charon/api/charons/${charonId}/submissions`, {params: {user_id: userId}})
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submissions.\n' + error, 'danger')
        })
    }

    static getNext(then) {
        axios.get(Submission.nextUrl)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submissions.\n' + error, 'danger')
        })
    }

    static update(charonId, submission, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/${submission.id}`, {submission: submission})
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error updating submission.\n' + error, 'danger')
        })
    }

    static findById(submissionId, userId, then) {
        axios.get(`/mod/charon/api/submissions/${submissionId}`, {params: {user_id: userId}})
            .then(response => {
                then(response.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submission.\n' + error, 'danger')
        })
    }

    static addNewEmpty(charonId, studentId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/add`, {student_id: studentId})
            .then(response => {
                then(response)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error adding new submission.\n' + error, 'danger')
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
                window.VueEvent.$emit('show-notification', 'Error retesting.\n' + error, 'danger')
            })
    }

    static findLatest(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/latest`)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving latest submissions.\n' + error, 'danger')
        })
    }

    static findSubmissionCounts(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/counts`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving Submission submission counts.\n' + error, 'danger')
        })
    }

    static findByUser(courseId, userId, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/users/${userId}/submissions`)
            .then(data => {
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submissions by user.\n' + error, 'danger')
        })
    }

    static findBestAverageCourseSubmissions(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/average`)
            .then(data => {
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving course average submissions.\n' + error, 'danger')
        })
    }

    static findAllSubmissionsForReport(courseId, serverParams, columnFilters, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/submissions-report/${serverParams.page}/` +
            `${serverParams.perPage}/` +
            `${serverParams.sort.field}/` +
            `${serverParams.sort.type}/` +
            `${serverParams.columnFilters.firstName ? serverParams.columnFilters.firstName : ' '}/` +
            `${serverParams.columnFilters.lastName ? serverParams.columnFilters.lastName : ' '}/` +
            `${serverParams.columnFilters.exerciseName ? serverParams.columnFilters.exerciseName : ' '}/` +
            `${serverParams.columnFilters.isConfirmed ? serverParams.columnFilters.isConfirmed : ' '}/` +
            `${serverParams.columnFilters.gitTimestampForStartDate ? serverParams.columnFilters.gitTimestampForStartDate : ' '}/` +
            `${serverParams.columnFilters.gitTimestampForEndDate ? serverParams.columnFilters.gitTimestampForEndDate : ' '}/`)
            .then(data => {
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving Submission submissions for report.\n' + error, 'danger')
        })
    }

    static findLatestSubmissionsByUser(courseId, userId, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/users/${userId}/latest-submissions`)
            .then(data => {
                then(data.data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving latest submissions by user.\n' + error, 'danger')
        })
    }

}

Submission.nextUrl = null
export default Submission
