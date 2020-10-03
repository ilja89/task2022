<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Registrations</v-card-title>
        </v-card>

        <defense-registrations-section :defense-list="defenseList" :apply="apply" :teachers="teachers"/>
    </div>

</template>

<script>

    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";
    import {mapState} from "vuex";
    import Defense from "../../../api/Defense";
    import Teacher from "../../../api/Teacher";

    export default {
        name: "defense-registrations-page",
        components: {DefenseRegistrationsSection},
        data() {
            return {
                defenseList: [],
                countDown: 0,
                teachers: []
            }
        },
        computed: {

            ...mapState([
                'course'
            ]),
        },

        created() {
            this.fetchRegistrations()
            Teacher.getAllTeachers(this.course.id, response => {
                this.teachers = response
            })
        },

        activated() {
            // TODO: when session is active - this.fetchRegistrations
            VueEvent.$on('refresh-page', this.fetchRegistrations)
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off('refresh-page', this.fetchRegistrations)
        },

        methods: {
            apply(after, before, filter_teacher, filter_progress) {
                Defense.filtered(this.course.id, after, before, filter_teacher, filter_progress, response => {
                    this.defenseList = response
                })
            },

            fetchRegistrations() {
                Defense.all(this.course.id, response => {
                    this.defenseList = response
                })
            }
        }
    }
</script>
