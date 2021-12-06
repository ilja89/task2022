import Vue from "vue";
import VueRouter from "vue-router";
import {SubmissionPage, SubmissionModal, SubmissionList} from "./components"
import Vuetify from "vuetify";

Vue.use(VueRouter);
Vue.use(Vuetify);

const routes = [
    {
        path: '/submissionPage/:submission_id',
        title: 'SubmissionPage',
        component: SubmissionPage,
        name: 'submission-page'
    },
    {
        path: '/'
    },

]
const router = new VueRouter({
    routes,
});

export default router;
