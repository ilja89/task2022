<template>
	<div class="student-overview-container">
		<page-title :student="student"></page-title>

		<popup-section title="Grades report"
									 subtitle="Grading report for the current student.">

			<v-card class="mx-auto" outlined light raised >
				<v-container class="spacing-playground pa-3" fluid>
					<div class="student-overview-card" v-html="table"></div>
				</v-container>
			</v-card>

		</popup-section>
	</div>
</template>

<script>
import {PageTitle} from '../partials'
import {mapState, mapGetters, mapActions} from 'vuex'
import {User} from '../../../api'
import {PopupSection} from '../layouts'

export default {

	components: {PopupSection, PageTitle},

	data() {
		return {
			table: '',
			CssOverflowWrapStyle:`<style type="text/css">.gradeitemheader {overflow-wrap:anywhere !important;word-break:break-all !important;}
th[class="level2 leveleven oddd2 b1b b1t column-itemname"]{overflow-wrap:anywhere !important;word-break:break-all !important;}
th[class="level2 leveleven evend2 b1b b1t column-itemname"]{overflow-wrap:anywhere !important;word-break:break-all !important;}</style>`,
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
			if (typeof this.routeStudentId !== 'undefined' && this.$route.name === 'student-overview') {
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
				this.table = this.CssOverflowWrapStyle + table;
			})
		},

		getStudent() {
			this.fetchStudent({courseId: this.courseId, studentId: this.routeStudentId})
		},
	},

	created() {
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
