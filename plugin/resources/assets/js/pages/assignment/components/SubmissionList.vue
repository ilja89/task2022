<template>
	<div>
		<v-data-table
			:expanded.sync="expanded" :headers="submissionHeaders" :items="submissionsTable"
			dense disable-filtering disable-pagination hide-default-footer item-key="id" show-expand single-expand
      :item-class="itemRowBackground">

			<template v-slot:footer>
				<v-toolbar flat class="mt-4">
					<v-btn v-if="canLoadMore" class="ml-4" color="primary" dense outlined text
						   @click="loadMoreSubmissions()">
						{{ translate('loadMoreText') }}
					</v-btn>
					
					<v-btn class="ml-4" icon @click="refreshSubmissions()">
						<img alt="refresh" height="24px" src="pix/refresh.png"
							 v-bind:class="refreshing ? 'rotating' : ''"
							 width="24px">
					</v-btn>
				</v-toolbar>
			</template>
			
			<template v-slot:expanded-item="{ headers, item }">
				<td :colspan="headers.length">
					<v-container fluid>
						<v-simple-table dense>
							<template v-slot:default>
								<tbody>
								<tr v-for="result in item.results" :key="getGrademapByResult(result).name">
									<td>{{ getGrademapByResult(result).name }}</td>
									<td>{{ result.calculated_result }} |
										{{ getGrademapByResult(result).grade_item.grademax|withoutTrailingZeroes }} |
										{{ getCompletionPercentage(result) }}%
									</td>
								</tr>

								<tr>
									<td>Points without reduction</td>
									<td>{{ pointsWithoutReduction(item) }}</td>
								</tr>
								</tbody>
							</template>
						</v-simple-table>
					</v-container>
				</td>
			</template>
			
			<template v-slot:item.string="{ item }">
				<v-chip :color="getColor(item)" dark large ripple>
					{{ submissionString(item) }}
				</v-chip>
			</template>

			<template v-slot:item.actions="{ item }">
				<v-row>
					<submission-modal :submission="item" :color="getColor(item)"/>
          <v-btn v-if="allow_submission > 0" icon @click="copyToEditor(item)">
            <img alt="copy to editor" height="24px" src="pix/copy.png" width="24px">
          </v-btn>
					<registration-bottom-sheet v-if="labs.length > 0" :submission="item" :color="getColor(item)"/>
				</v-row>
			</template>
		</v-data-table>
	</div>
</template>

<script>
import moment from "moment";
import {getSubmissionWeightedScore} from "../helpers/submission"
import {Translate} from "../../../mixins";
import {File, Submission} from "../../../api";
import RegistrationBottomSheet from "./RegistrationBottomSheet";
import SubmissionModal from "./SubmissionModal";
import {mapState} from "vuex";

export default {
	name: "Submission-list",
	
	mixins: [Translate],
	
	components: {
		RegistrationBottomSheet, SubmissionModal
	},

  props: {
    allow_submission: {required: true}
  },

	data() {
		return {
			expanded: [],
			canLoadMore: true,
			refreshing: false,
			submissionHeaders: [
				{text: 'submission', align: 'start', value: 'string'},
				{text: 'time', value: 'time'},
				{text: 'actions', value: 'actions', sortable: false},
				{text: '', value: 'data-table-expand'},
			],
		}
	},
	
	filters: {
		withoutTrailingZeroes(number) {
			return number.replace(/000$/, '');
		},
	},
	
	computed: {
		...mapState([
			'submissions',
			'grademaps',
			'registrations',
			'student_id',
			'charon',
			'labs'
		]),
		
		submissionsTable() {
			return this.submissions.map(submission => {
				const container = {...submission};
				container['time'] = this.formatDate(submission.created_at);
				return container;
			});
		}
	},

  mounted() {
    VueEvent.$on('add-submission', (submission) => {
      submission.latestAdded = true;
      this.$store.state.submissions.unshift(submission);
    });
  },
	
	methods: {

	  itemRowBackground(item) {
      return item.hasOwnProperty('latestAdded')
          && item.id === this.$store.state.submissions[0].id ? 'latest' : '';
    },

    copyToEditor(item) {
      File.findBySubmission(item.id, files => {
        VueEvent.$emit('change-editor-content', files);
      })
    },

		getColor(submission) {
			if (this.defendedSubmission(submission)) return 'success'
			else if (Number.parseFloat(getSubmissionWeightedScore(submission)) < 0.01) return 'red';
			else if (this.registeredSubmission(submission.id)) return 'teal';
			else return `light-blue darken-${this.getColorDarknessByPercentage(getSubmissionWeightedScore(submission) / 100)}`;
		},

		getColorDarknessByPercentage(percentage, maxDarkness = 3) {
			return maxDarkness - Math.floor(maxDarkness * percentage);
		},
		
		pointsWithoutReduction(submission) {
			return getSubmissionWeightedScore(submission) + "%"
		},
		
		defendedSubmission(submission) {
			try {
				const last = submission.results[submission.results.length - 1];
				return parseFloat(last['calculated_result']) !== 0.0 && last['grade_type_code'] === 1001;
			} catch (e) {
				return false
			}
		},
		
		formatDate(date) {
			return moment(date, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD HH:mm");
		},
		
		registeredSubmission(submissionId) {
			let test = this.registrations.find(x => x.submission_id === submissionId);
			if (test != null) {
				test = test['submission_id'];
				return submissionId === test;
			}
		},
		
		getCompletionPercentage(result) {
			return (100 * result.calculated_result / this.getGrademapByResult(result).grade_item.grademax)
				.toFixed(2);
		},
		
		getGrademapByResult(result) {
			let correctGrademap = null;
			this.grademaps.forEach(grademap => {
				if (grademap.grade_type_code === result.grade_type_code) {
					correctGrademap = grademap;
				}
			});
			return correctGrademap;
		},
		
		submissionString(submission) {
			let resultStr = '';
			let prefix = '';

			submission.results.forEach((result) => {
				resultStr += prefix;
				resultStr += result.calculated_result;
				prefix = ' | ';
			});
			return resultStr;
		},
		
		refreshSubmissions() {
			this.refreshing = true;
			Submission.findByUserCharon(this.student_id, this.charon.id, (submissions) => {
			  if (submissions.length === 0) {
          VueEvent.$emit('latest-submission-does-not-exist');
        } else {
          VueEvent.$emit('latest-submission-to-editor', submissions[0].id);
        }
        this.$store.state.submissions = submissions;
        this.canLoadMore = Submission.canLoadMore();
        this.refreshing = false;
			});
		},
		
		loadMoreSubmissions() {
			if (Submission.canLoadMore()) {
				Submission.getNext(submissions => {
					submissions.forEach(submission => this.$store.state.submissions.push(submission));
					this.canLoadMore = Submission.canLoadMore();
				});
			} else {
				this.canLoadMore = false;
			}
		},
	},
	
	watch: {
		charon() {
			this.refreshSubmissions();
		},
	},
}
</script>

<style>
.latest {
  background-color: #E8F3FA;
}
</style>

<style scoped>

.rotating {
	animation-name: spin;
	animation-duration: 1000ms;
	animation-iteration-count: infinite;
	animation-timing-function: linear;
}

@keyframes spin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(-360deg);
	}
}

</style>
