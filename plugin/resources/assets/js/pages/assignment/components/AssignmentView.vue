<template>
	<v-app>
		<v-main>
			<!-- moodle drawer has z-index 1000, navbar has z-index 2000 -->
			<v-snackbar v-model="notification.show" :timeout="notification.timeout" multi-line style="z-index: 3000">
				{{ notification.text }}

				<template v-slot:action="{ attrs }">
					<v-btn v-bind="attrs" color="blue" text @click="notification.show = false">
						{{ translate('closeText') }}
					</v-btn>
				</template>
			</v-snackbar>

      <submission-list
          :allow_submission="allow_submission">
      </submission-list>
          <assignment-body/>

			<v-row class="my-4">
				<student-registration-sheet/>

<!--				<registration-dialog/>-->
			</v-row>
		</v-main>
	</v-app>
</template>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<script>

import {Translate} from '../../../mixins';
import RegistrationDialog from "./RegistrationDialog";
import SubmissionList from "./SubmissionList";
import AssignmentBody from "./AssignmentBody";
import {mapState} from "vuex";
import StudentRegistrationSheet from "./StudentRegistrationSheet";

export default {

	name: "assignment-view",

	mixins: [Translate],

	components: {
		SubmissionList, RegistrationDialog, StudentRegistrationSheet, AssignmentBody
	},

  props: {
    allow_submission: {required: true}
  },

	data() {
		return {
			loaderVisible: 0,
			notification: {
				text: '',
				show: false,
				type: 'success',
				timeout: 1000,
			},
		};
	},

	methods: {
		initializeEventListeners() {
			VueEvent.$on('show-notification', (message, type = 'success', timeout = 2000) => {
				this.showNotification(message, type, timeout)
			});
			VueEvent.$on('close-notification', _ => this.notification.show = false)
			VueEvent.$on('show-loader', _ => this.loaderVisible += 1)
			VueEvent.$on('hide-loader', _ => this.hideLoader())
		},

		showNotification(message, type, timeout = 2000) {
			this.notification.text = message
			this.notification.show = true
			this.notification.type = type
			this.notification.timeout = timeout
		},

		hideLoader() {
			if (this.loaderVisible !== 0) {
				this.loaderVisible--
			}
		},

		getCharon() {
			axios.get(`api/charons/${this.charon_id}`).then(result => {
				this.$store.state.charon = result.data
			})
		},

		getLabs() {
			axios.get(`api/charons/${this.charon_id}/labs/view`).then(result => {
				this.$store.state.labs = result.data;
				this.$store.state.labs.sort((a, b) => {
					let ta = new Date(a.start),
						tb = new Date(b.start);
					return ta - tb;
				});
			});
		},

		getDefenseData() {
			axios.get(`api/charons/${this.charon_id}/registrations?id=${this.charon_id}&user_id=${this.student_id}`).then(result => {
				this.$store.state.registrations = result.data
			})
		},
	},

	computed: {
		...mapState([
			'charon_id',
			'student_id',
			'charon',
			'registrations',
			'labs'
		]),
	},


	created() {
		this.initializeEventListeners()

		this.getDefenseData();
		this.getCharon();
		this.getLabs();
	}

}
</script>

<style>
@import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
@import url("https://fonts.googleapis.com/css?family=Material+Icons");
</style>
