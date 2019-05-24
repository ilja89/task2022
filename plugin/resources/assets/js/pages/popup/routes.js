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
} from './pages';

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
];

const router = new VueRouter({
    routes,
    linkActiveClass: 'is-active',
});

export default router;
