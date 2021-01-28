<template>
	<v-card class="mx-auto mb-4">
		<v-card-text class="grey lighten-4">
			<v-container class="spacing-playground pa-3" fluid>
				<v-card-title v-if="registrations.length">
					{{ translate('myRegistrationsText') }}
					<v-spacer></v-spacer>

					<v-text-field
						v-if="registrations.length"
						v-model="search"
						append-icon="search"
						hide-details
						label="Search"
						single-line>
					</v-text-field>
				</v-card-title>

				<v-card-title v-else>
					{{ translate('noRegistrationsText') }}
				</v-card-title>

				<v-data-table
					:charon="charon"
					:headers="headers"
					:items="registrations"
					:registrations="registrations"
					:search="search"
					:student_id="student_id"
					class="elevation-1"
					multi-sort
					single-line>

					<template slot="no-data">
						<v-alert :value="true" style="text-align: center">
							{{ translate('tableNoRegistrationsText') }}
						</v-alert>
					</template>

					<template v-slot:item.actions="{ item }">
						<v-btn icon @click="deleteItem(item)">
							<img alt="eye" height="24px" src="pix/bin.png" width="24px">
						</v-btn>
					</template>
				</v-data-table>
			</v-container>
		</v-card-text>
	</v-card>
</template>

<script>

import moment from 'moment'
import {Translate} from '../../../mixins';
import Defense from "../../../api/Defense";
import {mapState} from "vuex";

export default {
	mixins: [Translate],

	name: "student-registrations",

	data() {
		return {
			search: '',
			singleSelect: false,
			dialog: false,
			headers: [
				{text: this.translate("charonText"), align: 'start', value: 'name'},
				{text: this.translate("timeText"), value: 'choosen_time'},
				{text: this.translate("teacherText"), value: 'teacher'},
				{text: this.translate("locationText"), value: 'teacher_location'},
				{text: this.translate("commentText"), value: 'teacher_comment'},
				{text: this.translate("actionsText"), value: 'actions', sortable: false},
			]
		}
	},

	methods: {
		deleteItem(item) {
			if (this.dateValidation(item)) {
				if (confirm(this.translate("registrationDeletionConfirmationText"))) {
					this.deleteReg(item);
				}
			} else {
				VueEvent.$emit('show-notification', this.translate("registrationBeforeErrorText"), 'danger')
			}
		},

		dateValidation(item) {
			const today = new Date();
			const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
			const time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
			const dateTime = date + ' ' + time;
			let day1 = moment.utc(dateTime, 'YYYY-MM-DD  HH:mm:ss');
			let day2 = moment.utc(item['choosen_time'], 'YYYY-MM-DD  HH:mm:ss');
			return day2.diff(day1, 'hours') >= 2;
		},

		deleteReg(defense_lab_item) {
			Defense.deleteStudentRegistration(this.charon.id, this.student_id, defense_lab_item['defense_lab_id'], defense_lab_item['submission_id'], (xs) => {
				const index = this.registrations.indexOf(defense_lab_item);
				if (index > -1) {
					this.registrations.splice(index, 1)
					VueEvent.$emit('show-notification', 'Deleted ' + xs + ' items successfully!', 'primary')
				}
				this.dialog = false
			})
		},
	},

	computed: {
		...mapState([
			'registrations',
			'student_id',
			'charon'
		]),
	},

}
</script>

<style>

.v-application--wrap {
	min-height: 1vh !important;
}

</style>