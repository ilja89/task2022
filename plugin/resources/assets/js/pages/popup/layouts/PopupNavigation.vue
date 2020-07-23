<template>
    <aside class="column is-2-desktop is-3-tablet  menu  nav-container">
        <ul class="menu-list">

            <li class="nav-item">
                <router-link to="/" exact>
                    Dashboard
                </router-link>
            </li>

            <li class="nav-item">
                <router-link :to="gradingLink">
                    Grading
                </router-link>
            </li>

            <li class="nav-item">
                <router-link :to="submissionLink()">
                    Submission
                </router-link>
            </li>

            <li class="nav-item">
                <router-link :to="studentOverviewLink">
                    Student overview
                </router-link>
            </li>

            <li class="nav-item">
                <router-link to="/plagiarism">
                    Plagiarism
                </router-link>
            </li>

            <li class="nav-item">
                <router-link to="/report-statistics">
                    Report & Statistics
                </router-link>
            </li>

            <li class="nav-item">
                <router-link to="/labs">
                    Labs
                </router-link>
            </li>

        </ul>
    </aside>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'

    export default {
        computed: {
            ...mapState([
                'student',
                'submission',
            ]),

            ...mapGetters([
                'submissionLink',
            ]),

            gradingLink() {
                if (this.student !== null) {
                    return '/grading/' + this.student.id
                } else {
                    return '/grading'
                }
            },

            studentOverviewLink() {
                if (this.student !== null) {
                    return '/student-overview/' + this.student.id
                } else {
                    return '/student-overview'
                }
            }
        }
    }
</script>
