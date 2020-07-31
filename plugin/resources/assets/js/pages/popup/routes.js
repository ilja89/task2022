import Vue from 'vue';
import VueRouter from 'vue-router';
import {
    NoStudentSelectedPage,
    SubmissionPage,
    GradingPage,
    StudentOverviewPage,
    DashboardPage,
    PlagiarismPage,
    ReportStatistics,
    LabsPage,
    DefenseSettingsPage,
    DefSettingsEditingPage
} from './pages';
import LabsForm from "./pages/labs/LabsForm";

Vue.use(VueRouter)

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
        path: '/defenseSettings',
        title: 'Defense Settings',
        component: DefenseSettingsPage,
        name: 'defense-settings-page'
    },
    {
        path: '/defSettingsEditing',
        title: 'Defense Settings Editing',
        component: DefSettingsEditingPage,
        name: 'def-settings-editing-page'
    }
];

const router = new VueRouter({
    routes,
    linkActiveClass: 'is-active',
});

export default router;
