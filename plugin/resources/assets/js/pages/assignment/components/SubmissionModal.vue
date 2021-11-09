<template>
	<v-dialog v-model="isActive" width="80%" style="position: relative; z-index: 3000"
			  transition="dialog-bottom-transition">
		<template v-slot:activator="{ on, attrs }">
                <v-btn icon :class="{ signal: notifyColor }" @click="onClickSubmissionInformation" v-bind="attrs" v-on="on">
				<v-icon aria-label="Submission Information" role="button" aria-hidden="false">mdi-eye</v-icon>
			</v-btn>
		</template>

		<v-card style="background-color: white; overflow-y: auto;">
			<v-toolbar :color="color" dark>
				<span class="headline">{{ translate('submissionText') }} {{ submission.git_hash }}</span>

				<v-spacer></v-spacer>

				<v-btn color="error" @click="isActive = false">
					{{ translate('closeText') }}
				</v-btn>
			</v-toolbar>

			<v-card-text class="pt-4">
				<div v-if="hasCommitMessage">
					<h3>{{ translate('commitMessageText') }}</h3>
					<p>{{ submission.git_commit_message }}</p>
				</div>

				<h3 v-if="toggleOn">Showing table</h3>
				<h3 v-else>Showing mail</h3>

				<label class="switch">
					<input type="checkbox" v-model="toggleOn">
					<span class="slider round"></span>
				</label>

				<div v-if="hasMail && !toggleOn">
					<h3>{{ translate('testerFeedbackText') }}</h3>
					<pre v-html="submission.mail"></pre>
				</div>
				<div v-if="toggleOn">
					<submission-table-component :testSuites="submission['test_suites']"></submission-table-component>
				</div>

				<h3>{{ translate('filesText') }}</h3>

				<files-component-without-tree :submission="submission" :testerType="testerType" :isRound="true">
				</files-component-without-tree>

				<div class="review-comments">
					<h3>{{ translate('feedbackText') }}</h3>
					<review-comment-component v-if="reviewCommentsExist" :files="files" view="student"></review-comment-component>
					<v-card v-else class="message">
						{{ translate('noFeedbackInfo') }}
					</v-card>
				</div>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>

<script>
import {FilesComponentWithoutTree} from '../../../components/partials'
import {Translate} from '../../../mixins'
import SubmissionTableComponent from "../../../components/partials/SubmissionTableComponent";
import {File, ReviewComment} from "../../../api";
import ReviewCommentComponent from "../../../components/partials/ReviewCommentComponent";
import {mapState} from "vuex";

export default {
	name: "submission-modal",

	mixins: [Translate],

	components: {
		ReviewCommentComponent, FilesComponentWithoutTree, SubmissionTableComponent
	},

	props: {
		submission: {required: true},
		color: {required: true}
	},

	data() {
		return {
			isActive: false,
			testerType: '',
			toggleOn: false,
			files: [],
			reviewCommentsExist: false,
			reviewCommentIdsWithNotify: [],
		}
	},

	computed: {
		...mapState([
			'charon_id',
			'student_id',
		]),

		hasCommitMessage() {
			return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0
		},

		hasMail() {
			return this.submission.mail !== null && this.submission.mail.length > 0
		},

		notifyColor() {
			return !!this.reviewCommentIdsWithNotify.length;
		}
	},

	mounted() {
		this.testerType = window.testerType
		this.getFiles()
		VueEvent.$on("student-refresh-submissions", this.getFiles);
	},

	methods: {
		getFiles() {
			File.findBySubmission(this.submission.id, files => {
				this.files = files
				this.checkComments();
			})
		},

		checkComments() {
			this.files.forEach(file => {
				if (file.review_comments.length > 0) {
					this.reviewCommentsExist = true;
					file.review_comments.forEach(reviewComment => {
						if (reviewComment.notify) {
							this.reviewCommentIdsWithNotify.push(reviewComment.id);
						}
					});
				}
			});
		},

		onClickSubmissionInformation() {
			this.isActive = true;
			if (this.reviewCommentIdsWithNotify.length) {
				ReviewComment.clearNotifications(
					this.reviewCommentIdsWithNotify, this.charon_id, this.student_id, () => {
						this.reviewCommentIdsWithNotify = [];
					});
			}
		}
	},
}
</script>
<style scoped>
@import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
@import url("https://fonts.googleapis.com/css?family=Material+Icons");
@import '../../../../css/toggleButton.css';

.review-comments {
	padding-top: 10px;
}

.signal {
    color: #f00!important;
}

</style>
