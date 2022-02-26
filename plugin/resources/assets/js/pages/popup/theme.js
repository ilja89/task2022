export default {
    primary: '#1976D2',
    secondary: '#424242',
    accent: '#82B1FF',
    error: '#FF5252',
    info: '#2196F3',
    success: '#4CAF50',
    warning: '#FFC107'
}

/**
 * Plagiarism network graph color labels
 * @type {string}
 */
export const NEUTRAL = '0-19'
export const INTERESTING = '20-39'
export const SUSPICIOUS = '40-59'
export const WARNING = '60-79'
export const DANGER = '80-100'

/**
 * Get the color group theme according to value for plagiarism network graph.
 * @param value from 0 to 1
 * @returns {string} group representing a color theme
 */
export const valueToGroup = (value) => {
    if (value < 20) {
        return NEUTRAL;
    } else if (value < 40) {
        return INTERESTING;
    } else if (value < 60) {
        return SUSPICIOUS;
    } else if (value < 80) {
        return WARNING;
    } else {
        return DANGER;
    }
}