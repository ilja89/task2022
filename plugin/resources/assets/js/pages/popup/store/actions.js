import { User } from '../../../api'

/**
 * Fetch the student by their id and the course id.
 * @param commit {Function}
 * @param studentId {Number}
 * @param courseId {Number}
 */
export const fetchStudent = ({ commit }, { studentId, courseId }) => {
    User.findById(courseId, studentId, user => {
        commit('UPDATE_STUDENT', { student: user })
    })
}

/**
 * Initialize the course with just the id.
 * @param commit {Function}
 * @param courseId {Number}
 */
export const initializeCourse = ({ commit }, { courseId }) => {
    const course = { id: courseId }

    commit('UPDATE_COURSE', { course })
}

/**
 * Update the charon in the store.
 * @param commit {Function}
 * @param charon {Object}
 */
export const updateCharon = ({ commit }, { charon }) => {
    commit('UPDATE_CHARON', { charon })
}

/**
 * Update the submission in the store.
 * @param commit {Function}
 * @param submission {Object}
 */
export const updateSubmission = ({ commit }, { submission }) => {
    commit('UPDATE_SUBMISSION', { submission })
}
