import VueRouter from 'vue-router';
import NoStudentSelectedPage from './pages/NoStudentSelectedPage.vue';
import SubmissionPage from './pages/SubmissionPage.vue';
import GradingPage from './pages/GradingPage.vue';

const routes = [
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
