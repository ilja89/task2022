<template>

    <v-navigation-drawer
            app
            permanent
            expand-on-hover
            clipped
            mini-variant
    >
        <v-list
                v-for="item in items"
                nav
        >
            <v-list-item nav :to="item.route">

                <v-list-item-icon>
                    <md-icon>{{ item.icon }}</md-icon>
                </v-list-item-icon>

                <v-list-item-content>
                    <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item-content>
            </v-list-item>
        </v-list>

    </v-navigation-drawer>

</template>

<script>
    import {mapState, mapGetters} from 'vuex'

    export default {
        data() {
            return {
                items: [
                    {title: 'Dashboard', icon: "dashboard", route: '/'},
                    {title: 'Grading', icon: 'grading', route: "" + this.gradingLink },
                    {title: 'Submission', icon: 'folder', route: "" + this.submissionLink },
                    {title: 'Student overview', icon: 'face', route: "" + this.studentOverviewLink },
                    {title: 'Plagiarism', icon: 'plagiarism', route: '/plagiarism'},
                    {title: 'Report & Statistics', icon: 'calculate', route: '/report-statistics'},
                    {title: 'Labs', icon: 'event_available', route: '/labs'},
                    {title: 'Defense settings', icon: 'settings', route: '/defenseSettings'},
                    {title: 'Defense registrations', icon: 'how_to_reg', route: '/defenseRegistrations'},
                ]
            }
        },
        computed: {
            ...mapState([
                'student',
                'submission',
            ]),

            ...mapGetters([
                'submissionLink',
            ]),

            gradingLink() {
                if (this.student != null) {
                    return '/grading/' + this.student.id
                } else {
                    return '/grading'
                }
            },

            studentOverviewLink() {
                if (this.student != null) {
                    return '/student-overview/' + this.student.id
                } else {
                    return '/student-overview'
                }
            }
        }
    }
</script>
