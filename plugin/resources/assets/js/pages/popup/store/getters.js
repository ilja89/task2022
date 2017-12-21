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
 * @param getters {Object}
 * @returns {String|null}
 */
export const studentsSearchUrl = (state, getters) => {
    return getters.courseId
        ? `/mod/charon/api/courses/${getters.courseId}/students/search`
        : null
}

/**
 * @name submissionLinkReturns
 * @function
 * @param {Number} submissionId=null
 * @return {String}
 */

/**
 * Get a link to a submission. If a submission ID is given, use that,
 * otherwise use the currently active submission.
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

/**
 * Get the link to the currently active Charon.
 * @param state {Object}
 * @returns {string}
 */
export const charonLink = state => state.charon
    ? `/mod/charon/view.php?id=${state.charon.course_module_id}`
    : null
