/**
 * Get the course id or null.
 * @param state {Object}
 * @returns {Number|null}
 */
export const courseId = state => {
    return state.course
        ? state.course.id
        : null
}

/**
 * Get the students search url for the current course.
 * @param state {Object}
 * @returns {String|null}
 */
export const studentsSearchUrl = state => {
    return state.course
        ? '/mod/charon/api/courses/' + state.course.id + '/students/search'
        : null
}

/**
 * @name submissionLinkReturns
 * @function
 * @param {Number} submissionId=null
 */

/**
 *
 * @param state {Object}
 * @returns {submissionLinkReturns}
 */
export const submissionLink = state => (submissionId = null) => {
    if (submissionId) {
        return `/submissions/${submissionId}`
    }

    if (state.submission) {
        return `/submissions/${state.submission.id}`
    }

    return '/submissions'
}
