/**
 * Format the name of a user.
 * @param user {{firstname: String, lastname: String, idnumber: String|null}}
 * @returns {String}
 */
export const formatName = user => {
    if (! user) return null

    return !user.idnumber
        ? `${user.firstname} ${user.lastname}`
        : `${user.firstname} ${user.lastname} (${user.idnumber})`
}

/**
 * Shorten the date by removing unnecessary seconds and milliseconds from the end.
 * @param date {String}
 * @returns {String}
 */
export const removeDateSeconds = date => date.replace(/:[0-9]{2}\.[0-9]+/, '')
