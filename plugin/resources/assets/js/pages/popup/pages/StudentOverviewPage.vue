<template>
    <div class="student-overview-container">
        <page-title :student="student"></page-title>

        <popup-section
            title="Grades report"
            subtitle="Grading report for the current student."
        >

            <div class="card  student-overview-card" v-html="table"></div>

        </popup-section>
    </div>
</template>

<script>
    import { mapState, mapGetters, mapActions } from 'vuex'
    import { PageTitle } from '../partials'
    import { User } from '../../../api'
    import { PopupSection } from '../layouts'

    export default {

        components: { PageTitle, PopupSection },

        data() {
            return {
                table: '',
            }
        },

        computed: {
            ...mapState([
                'student',
            ]),

            ...mapGetters([
                'courseId',
            ]),

            routeStudentId() {
                return this.$route.params.student_id
            },
        },

        watch: {
            $route() {
                if (typeof this.routeStudentId !== 'undefined' && this.$route.name == 'student-overview') {
                    this.getStudent()
                    this.getStudentOverviewTable()
                }
            },
        },

        methods: {
            ...mapActions([
                'fetchStudent',
            ]),

            getStudentOverviewTable() {
                User.getReportTable(this.courseId, this.routeStudentId, (table) => {
                    this.table = table
                })
            },

            getStudent() {
                this.fetchStudent({ courseId: this.courseId, studentId: this.routeStudentId })
            },
        },

        mounted() {
            this.getStudent()
            this.getStudentOverviewTable()
        },
    }
</script>

<style lang="scss">

    .student-overview-card {
        padding: 25px;
    }

    .b1l {
        padding: 0;
        width: 24px;
        min-width: 24px;
    }

    .student-overview-card {
        th {
            padding: 0.75em;

            img {
                margin-right: 15px;
            }
        }

        td {
            padding: 0.75em;
        }
    }

</style>
