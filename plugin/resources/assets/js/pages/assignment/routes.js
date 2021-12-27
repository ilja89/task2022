import Vue from "vue";
import VueRouter from "vue-router";
import {SubmissionPage} from "./pages"
import Vuetify from "vuetify";

Vue.use(VueRouter);
Vue.use(Vuetify);

const routes = [
    {
        path: '/submission-page/:submission_id',
        title: 'Submission Page',
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
