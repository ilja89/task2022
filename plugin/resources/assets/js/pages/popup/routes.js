import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuetify from "vuetify";

import {
    NoStudentSelectedPage,
    SubmissionPage,
    GradingPage,
    StudentOverviewPage,
    DashboardPage,
    PlagiarismPage,
    ReportStatistics,
    LabsPage,
    CharonSettingsPage,
    CharonSettingsEditingPage,
    DefenseRegistrationPage
} from './pages';
import LabsForm from "./pages/labs/LabsForm";

Vue.use(VueRouter)
Vue.use(Vuetify);

const routes = [
    {
        path: '/',
        title: 'Dashboard',
        component: DashboardPage,
        name: 'dashboard',
    },
    {
        path: '/submissions/:submission_id',
        title: 'Submission',
        component: SubmissionPage,
        name: 'submission',
    },
    {
        path: '/grading/:student_id',
        title: 'Grading',
        component: GradingPage,
        name: 'grading',
    },
    {
        path: '/student-overview/:student_id',
        title: 'Student overview',
        component: StudentOverviewPage,
        name: 'student-overview',
    },
    {
        path: '/plagiarism',
        title: 'Plagiarism',
        component: PlagiarismPage,
        name: 'plagiarism',
    },
    {
        path: '/report-statistics',
        title: 'Report & Statictics',
        component: ReportStatistics,
        name: 'report-statistics',
    },
    {
        path: '*',
        title: 'No student selected',
        component: NoStudentSelectedPage,
        name: 'no-student-selected'
    },
    {
        path: '/labs',
        title: 'Labs',
        component: LabsPage,
        name: 'labs'
    },
    {
        path: '/labsForm',
        title: 'Labs Form',
        component: LabsForm,
        name: 'labs-form',
    },
    {
        path: '/charonSettings',
        title: 'Charon Settings',
        component: CharonSettingsPage,
        name: 'defense-settings-page'
    },
    {
        path: '/defSettingsEditing',
        title: 'Charon Settings Editing',
        component: CharonSettingsEditingPage,
        name: 'def-settings-editing-page'
    },
    {
        path: '/defenseRegistrations',
        title: 'Defense Registrations',
        component: DefenseRegistrationPage,
        name: 'defense-registrations-page'
    }
];

const router = new VueRouter({
    routes,
    linkActiveClass: 'is-active',
});

export default router;
