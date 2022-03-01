class Submission {

    static getSubmissionDatesCountsForCharon(courseId, charonId, then) {
        window.axios.get(`/mod/charon/api/courses/${courseId}/charons/${charonId}/submissions-dates-counts`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submission dates counts for charon.\n' + error, 'danger')
        })
    }

    static getSubmissionCountsForCharonToday(courseId, charonId, then) {
        window.axios.get(`/mod/charon/api/courses/${courseId}/charons/${charonId}/submissions-counts-today`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving submission dates counts for charon.\n' + error, 'danger')
        })
    }

    static getCharonGeneralInformation(courseId, charonId, then) {
        window.axios.get(`/mod/charon/api/courses/${courseId}/charons/${charonId}/charon-general-information`)
            .then(({data}) => {
                then(data)
            }).catch(error => {
            VueEvent.$emit('show-notification', 'Error retrieving charon general information. \n' + error, 'danger')
        })
    }
}

Submission.nextUrl = null
export default Submission
