export const UPDATE_STUDENT = (state, { student }) => {
    state.student = student
}

export const UPDATE_TEACHER = (state, { teacher }) => {
    state.teacher = teacher
}

export const UPDATE_COURSE = (state, { course }) => {
    state.course = course
}

export const UPDATE_SUBMISSION = (state, { submission }) => {
    state.submission = submission
}

export const UPDATE_CHARON = (state, { charon }) => {
    state.charon = charon
}

export const UPDATE_LAB = (state, { lab }) => {
    state.lab = lab
}

export const UPDATE_LAB_TO_EMPTY = (state) => {
    state.lab = {start: {time: null}, end: {time: null}, teachers: []}
}
