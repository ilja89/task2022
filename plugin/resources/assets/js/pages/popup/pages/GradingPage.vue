<template>
    <div>

        <page-title :student="student"></page-title>

        <submissions-section></submissions-section>

        <comments-section></comments-section>

    </div>
</template>

<script>
    import { mapState, mapGetters, mapActions } from 'vuex'
    import { PageTitle } from '../partials'
    import { SubmissionsSection, CommentsSection } from '../sections'

    export default {
        components: { PageTitle, SubmissionsSection, CommentsSection },

        computed: {
            ...mapState([
                'student',
            ]),

            ...mapGetters([
                'courseId',
            ]),
        },

        mounted() {
            this.getStudent()
        },

        watch: {
            $route() {
                if (typeof this.$route.params.student_id !== 'undefined'
                    && this.student !== null
                    && this.student.id != this.$route.params.student_id
                ) {
                    this.getStudent()
                }
            },
        },

        methods: {
            ...mapActions([
                'fetchStudent',
                'updateSubmission',
            ]),

            getStudent() {
                const courseId = this.courseId
                const studentId = this.$route.params.student_id

                this.fetchStudent({ courseId, studentId })
                this.updateSubmission({ submission: null })
            },
        },
    }
</script>
