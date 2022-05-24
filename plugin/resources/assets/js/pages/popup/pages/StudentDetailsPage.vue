<template>
    <div class="student-overview-container">
        <page-title :title="studentName"></page-title>

        <student-details-charons-table-section :table="charonsTable"></student-details-charons-table-section>

        <student-summary-section :student_summary_data="student_summary"></student-summary-section>

        <student-details-submissions-section
        :latest-submissions="latestSubmissions"></student-details-submissions-section>

        <popup-section title="Upcoming registrations">
            <defense-registrations-section :teachers="teachers" :defense-list="defenseList"/>
        </popup-section>

        <student-charon-points-vs-course-average-chart
            v-if="student"
            :student="student"
            :average-submissions="averageSubmissions">
        </student-charon-points-vs-course-average-chart>

        <student-details-comment-section :studentId="this.student_id" :charons="this.charons"></student-details-comment-section>

        <popup-section title="Grades report"
                       subtitle="Grading report for the current student.">

            <v-card class="mx-auto" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>
                    <div class="student-overview-card" v-html="gradesTable"></div>
                </v-container>
            </v-card>

        </popup-section>
    </div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters} from 'vuex'
import {Charon, Defense, Submission, User} from '../../../api'
import {PopupSection} from '../layouts'
import StudentSummarySection from "../sections/StudentSummarySection";
import {
    DefenseRegistrationsSection,
    StudentDetailsSubmissionsSection,
    StudentDetailsCharonsTableSection,
    StudentDetailsCommentSection
} from '../sections'
import {StudentCharonPointsVsCourseAverageChart} from '../graphics'
import moment from "moment"
import Teacher from "../../../api/Teacher";

export default {
    components: {
        PopupSection,
        PageTitle,
        StudentSummarySection,
        StudentDetailsCommentSection,
        DefenseRegistrationsSection,
        StudentCharonPointsVsCourseAverageChart,
        StudentDetailsSubmissionsSection,
        StudentDetailsCharonsTableSection
    },

    name: "StudentDetailsPage",

    props: ['student_id'],

    data() {
        return {
            gradesTable: '',
            charonsTable: [],
            latestSubmissions: [],
            averageSubmissions: [],
            after: {time: `${moment().format("YYYY-MM-DD HH:mm")}`},
            before: {time: null},
            filter_teacher: -1,
            filter_progress: null,
            defenseList: [],
            teachers: [],
            name: 'Student name',
            student: null,
            table: '',
            student_summary: {
                'total_points_course': 0,
                'total_submissions': 0,
                'defended_charons': 0,
                'defence_registrations': 0,
                'charons_with_submissions': 0,
                'potential_points': 0
            },
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        ...mapState([
            'charons'
        ]),

        studentName() {
            return this.student ? this.student.firstname + ' ' + this.student.lastname + ' (' + this.student.username + ')' : "Student"
        }
    },

    methods: {
        getCharonsTable() {
            User.getUserCharonsDetails(this.courseId, this.student_id, data => {
                this.charonsTable = data
            })
        },

        getStudentOverviewTable() {
            User.getReportTable(this.courseId, this.student_id, (table) => {
                this.gradesTable = table
            })
        },

        getStudentSummary() {
            Charon.getAllPointsFromCourseForStudent(this.courseId, this.student_id, result => {
                this.student_summary['total_points_course'] = result
            })

            User.getPossiblePointsForCourse(this.courseId, this.student_id, result => {
                this.student_summary['potential_points'] = result
            })

            Submission.findAllForUser(this.courseId, this.student_id, result => {
                this.student_summary['total_submissions'] = result
            })

            Submission.findCharonsWithSubmissionsForUser(this.courseId, this.student_id, result => {
                this.student_summary['charons_with_submissions'] = result
            })

            Submission.findByUser(this.courseId, this.student_id, result => {
                this.student_summary['defended_charons'] = result.filter(sub => sub.finalgrade > 0).length
            })

            Defense.all(this.courseId, result => {
                this.student_summary['defence_registrations'] = result.filter(defense => defense.student_id === parseInt(this.student_id)).length
            })
        },

        fetchLatestSubmissions() {
            Submission.findLatestSubmissionsByUser(this.courseId, this.student_id, submissions => {
                this.latestSubmissions = submissions
            })
        },

        fetchRegistrations() {
            Defense.filtered(this.courseId, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
                this.defenseList = response.filter(defense => defense.student_id === parseInt(this.student_id))
            })
        },

        setAverageSubmissions(averageSubmissions) {
            this.averageSubmissions = averageSubmissions;
        }
    },

    created() {
        this.getStudentOverviewTable()
        this.getStudentSummary()
        this.getCharonsTable()
        this.fetchLatestSubmissions()
        this.fetchRegistrations()
        Teacher.getAllTeachers(this.courseId, response => {
            this.teachers = response
        })
        Submission.findBestAverageCourseSubmissions(this.courseId, this.setAverageSubmissions)
        User.getStudentInfo(this.courseId, this.student_id, response => {
            this.student = response
        })
    },

    metaInfo() {
        return {
            title: this.studentName + ' details page'
        }
    }
}
</script>

<style scoped>
</style>