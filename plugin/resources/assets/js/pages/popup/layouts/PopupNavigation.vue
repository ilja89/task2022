<template>

    <v-navigation-drawer
            app
            :value="drawer"
            :permanent="!is_mobile"
            v-click-outside="closeDrawer"
            :expand-on-hover="!is_mobile"
            clipped
            :mini-variant="!is_mobile">
        <v-list v-for="item in items" v-bind:key="item" nav>

            <v-list-item v-if="item.external" nav :href="item.route()">
                <v-list-item-icon>
                    <md-icon>{{ item.icon }}</md-icon>
                </v-list-item-icon>

                <v-list-item-content>
                    <v-list-item-title>{{ item.title }}</v-list-item-title>
                </v-list-item-content>
            </v-list-item>

            <v-list-item v-else nav :to="item.route()">
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
    import store from './../store/index'
    import {mapState, mapGetters} from 'vuex'

    export default {
        data() {
            return {
                items: [
                    {title: 'Dashboard', icon: "dashboard", route: () => '/'},
                    {title: 'Grading', icon: 'grading', route: this.gradingLink},
                    {title: 'Student overview', icon: 'face', route: this.studentOverviewLink},
                    {title: 'Plagiarism', icon: 'plagiarism', route: () => '/plagiarism'},
                    {title: 'Report & Statistics', icon: 'calculate', route: () => '/report-statistics'},
                    {title: 'Labs', icon: 'event_available', route: () => '/labs'},
                    {title: 'Charon settings', icon: 'settings', route: () => '/charonSettings'},
                    {title: 'Defense registrations', icon: 'how_to_reg', route: () => '/defenseRegistrations'},
                    {title: 'Teacher overview', icon: 'school', route: () => '/teachers'},
                    {title: window.course_shortname, icon: 'home', route: () => this.courseLink, external: true},
                ]
            }
        },
        computed: {
            ...mapState([
                'is_mobile',
                'drawer',
                'student',
                'submission',
            ]),
            ...mapGetters([
                'submissionLink',
                'courseLink'
            ]),

        },

        mounted() {
            store.state.is_mobile = window.innerWidth <= 480;
            window.addEventListener('resize', function () {
                store.state.is_mobile = window.innerWidth <= 480;
            });
        },

        methods: {
            closeDrawer() {
                store.state.drawer = false
            },

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
