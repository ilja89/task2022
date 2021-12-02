import Vue from 'vue'
import Vuex from 'vuex'
import Vuetify from "vuetify";

Vue.use(Vuex)
Vue.use(Vuetify, {
    iconfont: 'md'
})

const charon_id = window.charonId
const student_id = window.studentId
const w_grademaps = window.grademaps

const store = new Vuex.Store({
    state: {
        charon_id: charon_id,
        student_id: student_id,
        charon: Object,
        grademaps: w_grademaps,
        charons: [],
        submissions: [],
        registrations: [],
        labs: [],
        filesWithReviewComments: [],
    }
})

export default store
