<template>
    <div class="student-overview-container">
        <page-title :student="student"></page-title>

        <popup-section
                title="Grades report"
                subtitle="Grading report for the current student.">

            <div class="card student-overview-card" v-html="table"></div>

        </popup-section>

    </div>
</template>

<script>
    import { PageTitle } from '../partials'
    import { User } from '../../../models'
    import { PopupSection } from '../layouts'

    export default {

        components: { PageTitle, PopupSection },

        props: {
            context: { required: true },
        },

        data() {
            return {
                table: '',
                student: null,
            }
        },

        watch: {
            $route() {
                if (typeof this.$route.params.student_id !== 'undefined') {
                    this.getStudent()
                    this.getStudentOverviewTable()
                }
            }
        },

        methods: {
            getStudentOverviewTable() {
                User.getReportTable(this.context.course_id, this.$route.params.student_id, (table) => {
                    this.table = table
                })
            },

            getStudent() {
                User.findById(this.context.course_id, this.$route.params.student_id, (user) => {
                    this.student = user
                })
            }
        },

        mounted() {
            this.getStudent()
            this.getStudentOverviewTable()
        }
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
