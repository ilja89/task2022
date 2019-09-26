import Vue from 'vue'
import Vuex from 'vuex'

import * as actions from './actions'
import * as mutations from './mutations'
import * as getters from './getters'

Vue.use(Vuex)

const initialCourse = { id: window.course_id }

const store = new Vuex.Store({
    state: {
        /**
         * @type {
         *      {id: Number, firstname: String, lastname: String, groups: Array<Object>}
         * |null}
         */
        student: null,
        /**
         * @type {{
         *      id: Number,
         *      order_nr: String,
         * }|null}
         */
        submission: null,
        /**
         * @type {{
         *      id: Number,
         *      calculation_formula: String,
         *      category_id: Number,
         *      course: Number,
         *      course_module_id: Number,
         *      deadlines: Array<Object>,
         *      grademaps: Array<Object>,
         *      name: String,
         *      project_folder: String,
         *      tester_type_name: String,
         * }|null}
         */
        charon: null,
        /**
         * @type {
         *      {id: Number}
         * |null}
         */
        course: initialCourse,
    },
    getters,
    mutations,
    actions,
})

export default store
