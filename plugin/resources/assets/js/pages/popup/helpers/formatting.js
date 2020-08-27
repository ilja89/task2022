/**
 * Format the name of a user.
 * @param user {{firstname: String, lastname: String, idnumber: String|null}}
 * @returns {String}
 */
export const formatName = user => {
    if (!user) return null

    return !user.idnumber
        ? `${user.firstname} ${user.lastname}`
        : `${user.firstname} ${user.lastname} (${user.idnumber})`
}

/**
 * Shorten the date by removing unnecessary seconds and milliseconds from the end.
 * @param date {String}
 * @returns {String}
 */
export const removeDateSeconds = date => {
    try {
        date.replace(/:[0-9]{2}\.[0-9]+/, '')
    } catch (e) {
        return date
    }
}

/**
 * Format the results of a Submission into a string.
 * @param submission {{results: {calculated_result: String}[]}}
 * @param separator {string}
 * @returns {String}
 */
export const formatSubmissionResults = (submission, separator = ' | ') => submission.results
    .map(result => result.calculated_result)
    .join(separator)

/**
 * Format the given deadline to a string.
 * @param deadline {Object}
 * @returns {String}
 */
export const formatDeadline = deadline => {
    const date = removeDateSeconds(deadline.deadline_time.date)
    const percentage = deadline.percentage
    const groupName = deadline.group
        ? deadline.group.name
        : 'All groups'

    return `${date} - ${percentage}% (${groupName})`
}
