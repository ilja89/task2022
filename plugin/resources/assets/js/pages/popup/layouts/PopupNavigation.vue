<template>

    <v-navigation-drawer
            permanent
            expand-on-hover
            app
            clipped
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
                    {title: 'Dashboard', icon: "mdi-cached", route: '/'},
                    {title: 'Grading', icon: 'mdi_cached', route: "" + this.gradingLink },
                    {title: 'Submission', icon: 'cached', route: "" + this.submissionLink },
                    {title: 'Student overview', icon: 'dashboard', route: "" + this.studentOverviewLink },
                    {title: 'Plagiarism', icon: 'move_to_inbox', route: '/plagiarism'},
                    {title: 'Report & Statistics', icon: 'mdi-move_to_inbox', route: '/report-statistics'},
                    {title: 'Labs', icon: 'dashboard', route: '/labs'},
                    {title: 'Defense settings', icon: 'dashboard', route: '/defenseSettings'},
                    {title: 'Defense registrations', icon: 'dashboard', route: '/defenseRegistrations'},
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
