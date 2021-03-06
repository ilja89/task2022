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
        },

        methods: {
            setCharons(charons) {
                this.charons = charons;
            },

            setAverageSubmissions(averageSubmissions) {
                this.averageSubmissions = averageSubmissions;
            },
        },
    }
</script>
