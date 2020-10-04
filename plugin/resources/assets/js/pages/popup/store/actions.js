import {User} from '../../../api'

/**
 * Fetch the student by their id and the course id.
 * @param commit {Function}
 * @param studentId {Number}
 * @param courseId {Number}
 */
export const fetchStudent = ({commit}, {studentId, courseId}) => {
    return new Promise((resolve, reject) => {
        if (studentId === undefined || courseId === undefined) {
            reject(null)
        }

        User.getStudentInfo(courseId, studentId, user => {
            commit('UPDATE_STUDENT', {student: user});
            resolve(user);
        })

    })
}

/**
 * Initialize the course with just the id.
 * @param commit {Function}
 * @param courseId {Number}
 */
export const initializeCourse = ({commit}, {courseId}) => {
    const course = {id: courseId}

    commit('UPDATE_COURSE', {course})
}

/**
 * Update the charon in the store.
 * @param commit {Function}
 * @param charon {Object}
 */
export const updateCharon = ({commit}, {charon}) => {
    commit('UPDATE_CHARON', {charon})
}

/**
 * Update the teacher in the store.
 * @param commit {Function}
 * @param teacher {Object|null}
 */
export const updateTeacher = ({commit}, {teacher}) => {
    commit('UPDATE_TEACHER', {teacher})
}

/**
 * Update the submission in the store.
 * @param commit {Function}
 * @param submission {Object}
 */
export const updateSubmission = ({commit}, {submission}) => {
    commit('UPDATE_SUBMISSION', {submission})
}

/**
 * Update the lab in the store.
 * @param commit {Function}
 * @param lab {Object}
 */
export const updateLab = ({commit}, {lab}) => {
    commit('UPDATE_LAB', {lab})
}

/**
 * Update the lab in the store to null.
 * @param commit {Function}
 * @param submission {Object}
 */
export const updateLabToEmpty = ({commit}) => {
    commit('UPDATE_LAB_TO_EMPTY')
}
