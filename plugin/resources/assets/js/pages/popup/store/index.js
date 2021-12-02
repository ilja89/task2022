import Vue from 'vue'
import Vuex from 'vuex'
import Vuetify from "vuetify";

import * as actions from './actions'
import * as mutations from './mutations'
import * as getters from './getters'

Vue.use(Vuex)
Vue.use(Vuetify, {
    iconfont: 'md'
})

const initialCourse = {id: window.course_id}

const store = new Vuex.Store({
    state: {
        /**
         * @type Boolean
         * **/
        is_mobile: false,
        /**
         * @type Boolean
         * **/
        drawer: false,
        /**
         * @type {
         *      {id: Number, firstname: String, lastname: String, idnumber: String, groups: Array<Object<Array<Object>>>}
         * |null}
         */
        student: null,
        /**
         * @type {
         *      {id: Number, start: Date, end: Date, name: String, teachers: Array<Object>}
         * |null}
         */
        lab: null,
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
         * @type == [@Charon]
         * */
        charons: [],
        /**
         * @type {
         *      {id: Number}
         * |null}
         */
        course: initialCourse,
        /**
         * @type {
         *      {id: Number, firstname: String, lastname: String, fullname: String}
         * |null}
         */
        teacher: null,
        /**
         * @type {
         *      {fileId: Number,
         *      studentId: number,
         *      submissionId: number,
         *      path: String,
         *      reviewComments: Array<Object>}
         * |null}
         */
        filesWithReviewComments: null,
    },
    getters,
    mutations,
    actions,
})

export default store
