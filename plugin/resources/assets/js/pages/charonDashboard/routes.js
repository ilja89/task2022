import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuetify from "vuetify";

import DashboardPage from "./pages/DashboardPage";

Vue.use(VueRouter)
Vue.use(Vuetify);

const routes = [
    {
        path: '/',
        title: 'Dashboard',
        component: DashboardPage,
        name: 'dashboard',
    }
];

const router = new VueRouter({
    routes,
    linkActiveClass: 'is-active',
});

export default router;
