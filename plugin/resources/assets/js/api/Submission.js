import {Error} from "./index";

class Submission {

    static getTemplates(charonId, then) {
        return axios.get(`/mod/charon/api/charons/${charonId}/templates`)
            .then(response => {
            then(response.data)
        }).catch(error => {
            Error.throw(error, 'Error getting templates.\n')
        })
    }

    static submitSubmission(sourceFiles, charonId, then) {
        axios.post(`/mod/charon/api/submissions/${charonId}/postSubmission`, {
                sourceFiles: sourceFiles
            }).then(response => {
                then(response.data)
            }).catch(error => {
                VueEvent.$emit('reset-submit-button')
                Error.throw(error, 'Error saving submission.\n')
            })
    }

    static findByUserCharon(userId, charonId, then) {
        axios.get(`/mod/charon/api/charons/${charonId}/submissions`, {params: {user_id: userId}})
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving submissions.\n')
            })
    }

    static getNext(then) {
        axios.get(Submission.nextUrl)
            .then(({data}) => {
                Submission.nextUrl = data.next_page_url
                then(data.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving submissions.\n')
            })
    }

    static update(charonId, submission, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/${submission.id}`, {submission: submission})
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error updating submission.\n')
            })
    }

    static findById(submissionId, userId, then) {
        axios.get(`/mod/charon/api/submissions/${submissionId}`, {params: {user_id: userId}})
            .then(response => {
                then(response.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving submission.\n')
            })
    }

    static addNewEmpty(charonId, studentId, then) {
        axios.post(`/mod/charon/api/charons/${charonId}/submissions/add`, {student_id: studentId})
            .then(response => {
                then(response)
            }).catch(error => {
                Error.throw(error, 'Error adding new submission.\n')
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
                Error.throw(error, 'Error retrieving latest submissions.\n')
            })
    }

    static findSubmissionCounts(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/counts`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving Submission submission counts.\n')
            })
    }

    static findByUser(courseId, userId, then) {
        axios.get(`/mod/charon/api/courses/${courseId}/users/${userId}/submissions`)
            .then(data => {
                then(data.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving submissions by user.\n')
            })
    }

    static findBestAverageCourseSubmissions(courseId, then) {
        window.axiosNoLoading.get(`/mod/charon/api/courses/${courseId}/submissions/average`)
            .then(data => {
                then(data.data)
            }).catch(error => {
                Error.throw(error, 'Error retrieving course average submissions.\n')
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
                Error.throw(error, 'Error retrieving Submission submissions for report.\n')
            })
    }

}

Submission.nextUrl = null
export default Submission
