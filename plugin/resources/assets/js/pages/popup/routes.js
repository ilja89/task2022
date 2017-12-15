import VueRouter from 'vue-router';
import { NoStudentSelectedPage, SubmissionPage, GradingPage, StudentOverviewPage, DashboardPage } from './pages';

const routes = [
    {
        path: '/',
        title: 'Dashboard',
        component: DashboardPage,
        name: 'dashboard',
    },
    {
        path: '/submission/:submission_id',
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
        path: '*',
        title: 'No student selected',
        component: NoStudentSelectedPage,
        name: 'no-student-selected'
    }
];

const router = new VueRouter({
    routes,
    linkActiveClass: 'is-active',
});

export default router;
