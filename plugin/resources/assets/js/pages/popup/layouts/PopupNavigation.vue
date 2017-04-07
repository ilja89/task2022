<template>
    <aside class="column is-2  menu  nav-container">
        <ul class="menu-list">

            <li class="nav-item">
                <router-link :to="gradingLink">
                    Grading
                </router-link>
            </li>

            <li class="nav-item">
                <router-link :to="submissionLink">
                    Submission
                </router-link>
            </li>

            <li class="nav-item">
                <router-link :to="studentOverviewLink">
                    Student overview
                </router-link>
            </li>

        </ul>
    </aside>
</template>

<script>
    export default {
        props: {
            context: { required: true },
        },

        computed: {
            gradingLink() {
                if (this.context.active_student !== null) {
                    return '/grading/' + this.context.active_student.id;
                } else {
                    return '/grading';
                }
            },

            submissionLink() {
                if (this.context.active_submission !== null) {
                    return '/submission/' + this.context.active_submission.id;
                } else {
                    return '/submission';
                }
            },

            studentOverviewLink() {
                if (this.context.active_student !== null) {
                    return '/student-overview/' + this.context.active_student.id;
                } else {
                    return '/student-overview';
                }
            }
        }
    }
</script>
