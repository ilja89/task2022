<template>
    <div>

        <page-title :student="student"></page-title>

        <submissions-section></submissions-section>

        <student-charon-points-vs-course-average-chart
                v-if="student"
                :student="student"
                :charons="charonsData"
                :average-submissions="averageSubmissions">
        </student-charon-points-vs-course-average-chart>

        <comments-section></comments-section>

    </div>
</template>

<script>
    import {mapState, mapGetters, mapActions} from 'vuex'
    import {PageTitle} from '../partials'
    import {SubmissionsSection, CommentsSection} from '../sections'
    import {Charon, Submission} from '../../../api'
    import {StudentCharonPointsVsCourseAverageChart} from '../graphics';

    export default {
        components: {PageTitle, SubmissionsSection, CommentsSection, StudentCharonPointsVsCourseAverageChart,},

        computed: {
            ...mapState([
                'student',
                'charon'
            ]),

            ...mapGetters([
                'courseId',
            ]),
            charonsData() {
                return this.charons.map(c => {
                    return {name: c.name, id: c.id};
                });
            },
        },

        data() {
            return {
                charons: [],
                averageSubmissions: []
            }
        },

        created() {
            Submission.findBestAverageCourseSubmissions(this.courseId, this.setAverageSubmissions)
            this.getStudent()
            window.VueEvent.$on('refresh-page', this.getStudent)
        },

        beforeDestroy() {
            window.VueEvent.$off('refresh-page', this.getStudent)
        },

        watch: {
            $route() {
                if (typeof this.$route.params.student_id != 'undefined'
                    && this.student != null
                    && this.student.id != this.$route.params.student_id
                ) {
                    this.getStudent()
                }
            },
        },

        methods: {
            ...mapActions([
                'fetchStudent',
                'updateSubmission'
            ]),

            getStudent() {
                const courseId = this.courseId;
                const studentId = this.$route.params.student_id;

                this.fetchStudent({courseId, studentId});
                Charon.all(courseId, this.setCharons);
                this.updateSubmission({submission: null})
            },

            setCharons(charons) {
                this.charons = charons;
            },

            setAverageSubmissions(averageSubmissions) {
                this.averageSubmissions = averageSubmissions;
            },
        },
    }
</script>
