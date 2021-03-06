<template>
	<v-dialog v-model="dialog" fullscreen hide-overlay style="position: relative; z-index: 2000"
	          transition="dialog-bottom-transition">
		<template v-slot:activator="{ on, attrs }">
			<v-btn v-bind="attrs" v-on="on" class="mt-4 ml-4" color="primary" dense outlined @click="dialog=true">
				<!-- TODO: translate -->
				New registration
			</v-btn>
		</template>

		<v-card>
			<v-card class="mx-auto">
				<v-sheet class="pa-4 primary lighten-2">
					<v-text-field v-model="search" clear-icon="mdi-close-circle-outline" clearable dark flat
					              hide-details label="Search Company Directory" solo-inverted/>
				</v-sheet>

				<v-card-text>
					<v-row>
						<v-col>
							<v-treeview v-model="submissionSelection" :active.sync="active" :items="items"
							            :search="search" activatable color="indigo"
							            dense expand-icon="mdi-chevron-down"
							            indeterminate-icon="mdi-shield-plus" off-icon="mdi-shield-outline"
							            on-icon="mdi-shield" open-on-click return-object selectable transition>

								<template v-slot:prepend="{ item }">
									<v-icon v-if="item.children"
									        v-text="`mdi-${item.id === 1 ? 'git' : 'folder-network'}`"/>
								</template>
							</v-treeview>
						</v-col>

						<v-divider vertical></v-divider>

						{{ active }}

						<v-col class="pa-6" cols="6">
							<template v-if="!submissionSelection.length">
								No nodes selected.
							</template>

							<template v-else>
								TODO some info about submission:
								<div v-for="node in submissionSelection" :key="node.id">
									{{ node.name }}
								</div>
							</template>
						</v-col>
					</v-row>

					<v-row class="fill-height">
						<v-col>
							<v-sheet height="64">
								<v-toolbar flat>
									<v-btn class="mr-4" color="grey darken-2" outlined @click="setToday">
										Today
									</v-btn>

									<v-btn color="grey darken-2" fab small text @click="prev">
										<v-icon small>
											mdi-chevron-left
										</v-icon>
									</v-btn>

									<v-btn color="grey darken-2" fab small text @click="next">
										<v-icon small>
											mdi-chevron-right
										</v-icon>
									</v-btn>

									<v-toolbar-title v-if="$refs.calendar">
										{{ $refs.calendar.title }}
									</v-toolbar-title>

									<v-spacer></v-spacer>

									<v-menu bottom right>
										<template v-slot:activator="{ on, attrs }">
											<v-btn v-bind="attrs" v-on="on" color="grey darken-2" outlined>
												<span>{{ typeToLabel[type] }}</span>

												<v-icon right>
													mdi-menu-down
												</v-icon>
											</v-btn>
										</template>

										<v-list>
											<v-list-item @click="type = 'day'">
												<v-list-item-title>Day</v-list-item-title>
											</v-list-item>

											<v-list-item @click="type = 'week'">
												<v-list-item-title>Week</v-list-item-title>
											</v-list-item>

											<v-list-item @click="type = 'month'">
												<v-list-item-title>Month</v-list-item-title>
											</v-list-item>

											<v-list-item @click="type = '4day'">
												<v-list-item-title>4 days</v-list-item-title>
											</v-list-item>
										</v-list>
									</v-menu>
								</v-toolbar>
							</v-sheet>

							<v-sheet height="600">
								<v-calendar
									ref="calendar"
									v-model="focus"
									:event-color="getEventColor"
									:events="events"
									:type="type"
									color="primary"
									@change="updateRange"
									@click:event="showEvent"
									@click:more="viewDay"
									@click:date="viewDay"
								></v-calendar>

								<v-menu v-model="selectedOpen" :activator="selectedElement"
								        :close-on-content-click="false" offset-x>
									<v-card color="grey lighten-4" flat min-width="350px">
										<v-toolbar :color="selectedEvent.color" dark>
											<v-toolbar-title v-html="selectedEvent.name"></v-toolbar-title>
										</v-toolbar>

										<v-card-text>
											Register {{this.submissionSelection}} for {{this.selectedEvent}}?
										</v-card-text>

										<v-card-actions>
											<v-btn color="primary" text @click="registerSelected()">
												Register
											</v-btn>
											
											<v-btn color="secondary" text @click="selectedOpen = false">
												Cancel
											</v-btn>
										</v-card-actions>
									</v-card>
								</v-menu>
							</v-sheet>
						</v-col>
					</v-row>
				</v-card-text>
			</v-card>
		</v-card>
	</v-dialog>
</template>

<script>
export default {
	name: "registration-dialog",

	data() {
		return {
			dialog: false,
			notifications: false,
			sound: true,
			widgets: false,

			// tree view
			active: [],
			submissionSelection: [],
			search: null,
			items: [
				{
					id: 1,
					name: 'Submissions',
					children: [
						{
							id: 2,
							name: 'EX12',
							children: [
								{
									id: 201,
									git_timestamp: "2020-12-05 18:23:59",
									charon_id: 12,
									test_suites: [
										{
											weight: 1,
											grade: 100
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 100,
									name: 'EX12 - 2020-12-05 18:23:59 - 100%',
								},
								{
									id: 202,
									git_timestamp: "2020-12-06 18:23:59",
									charon_id: 12,
									test_suites: [
										{
											weight: 1,
											grade: 90
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 90,
									name: 'EX12 - 2020-12-06 18:23:59 - 90%',
								},
								{
									id: 203,
									git_timestamp: "2020-12-07 18:23:59",
									charon_id: 12,
									test_suites: [
										{
											weight: 1,
											grade: 80
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 80,
									name: 'EX12 - 2020-12-07 18:23:59 - 80%',
								},
								{
									id: 204,
									git_timestamp: "2020-12-08 18:23:59",
									charon_id: 12,
									test_suites: [
										{
											weight: 1,
											grade: 40
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 40,
									name: 'EX12 - 2020-12-08 18:23:59 - 40%',
								},
								{
									id: 205,
									git_timestamp: "2020-12-09 18:23:59",
									charon_id: 12,
									test_suites: [
										{
											weight: 1,
											grade: 50
										},
										{
											weight: 1,
											grade: 70
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 60,
									name: 'EX12 - 2020-12-09 18:23:59 - 60%',
								},
							],
						},
						{
							id: 3,
							name: 'EX13',
							children: [
								{
									id: 301,
									git_timestamp: "2021-1-05 18:23:59",
									charon_id: 13,
									test_suites: [
										{
											weight: 1,
											grade: 30
										},
										{
											weight: 1,
											grade: 70
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 50,
									name: 'EX13 - 2021-1-05 18:23:59 - 50%',
								},
								{
									id: 302,
									git_timestamp: "2021-1-06 18:23:59",
									charon_id: 13,
									test_suites: [
										{
											weight: 1,
											grade: 90
										},
										{
											weight: 1,
											grade: 70
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 80,
									name: 'EX13 - 2021-1-06 18:23:59 - 80%',
								},
							],
						},
						{
							id: 4,
							name: 'EX14',
							children: [
								{
									id: 401,
									git_timestamp: "2021-2-05 18:23:59",
									charon_id: 14,
									test_suites: [
										{
											weight: 1,
											grade: 10
										},
										{
											weight: 1,
											grade: 10
										},
										{
											weight: 1,
											grade: 10
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 10,
									name: 'EX13 - 2021-2-05 18:23:59 - 10%',
								},
								{
									id: 402,
									git_timestamp: "2021-2-06 18:23:59",
									charon_id: 14,
									test_suites: [
										{
											weight: 3,
											grade: 10
										},
										{
											weight: 4,
											grade: 30
										},
										{
											weight: 1,
											grade: 10
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 0,
									name: 'EX14 - 2021-2-06 18:23:59 - 0%',
								},
								{
									id: 403,
									git_timestamp: "2021-2-07 18:23:59",
									charon_id: 14,
									test_suites: [
										{
											weight: 3,
											grade: 10
										},
										{
											weight: 4,
											grade: 10
										},
										{
											weight: 1,
											grade: 10
										},
									],
									results: [{
										id: 752,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 1
									}, {
										id: 751,
										submission_id: 225,
										calculated_result: "1.00",
										grade_type_code: 101
									}, {
										id: 753,
										submission_id: 225,
										calculated_result: "0.00",
										grade_type_code: 1001
									}],
									result: 10,
									name: 'EX14 - 2021-2-07 18:23:59 - 10%',
								},
							],
						},
					],
				},
			],

			// calendar
			focus: '',
			type: 'month',
			typeToLabel: {
				month: 'Month',
				week: 'Week',
				day: 'Day',
				'4day': '4 Days',
			},
			selectedEvent: {},
			selectedElement: null,
			selectedOpen: false,
			events: [],

			// generation
			colors: ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'grey darken-1'],
			names: ['Meeting', 'Holiday', 'PTO', 'Travel', 'Event', 'Birthday', 'Conference', 'Party'],
		}
	},

	computed: {},

	watch: {
		submissionSelection: function (updatedSubmissionSelection) {
			let maxSubs = {}
			let sub;

			for (sub of updatedSubmissionSelection) {
				if (!maxSubs[sub.charon_id] || maxSubs[sub.charon_id].result < sub.result) {
					if (sub.result >= 50) { //TODO: check if style OK and result over thresh hold and is not defended already
						maxSubs[sub.charon_id] = sub
					}
				}
			}

			const newSubmissionSelection = Object.keys(maxSubs).map(key => maxSubs[key]);

			if (newSubmissionSelection.length + 1 === updatedSubmissionSelection.length) {
				VueEvent.$emit('show-notification', `TODO: reason why choice is not possible or suboptimal`, 'warning')
			}

			for (let i = 0; i < this.submissionSelection.length; ++i) {
				if (!newSubmissionSelection[i] || !this.submissionSelection[i] || newSubmissionSelection[i].id !== this.submissionSelection[i].id) {
					this.submissionSelection = newSubmissionSelection;
					return;
				}
			}
		}
	},

	methods: {
		registerSelected() {
			VueEvent.$emit('show-notification', this.submissionSelection + 'was registered for event: ' + this.selectedEvent , 'info')
		},
		
		viewDay({date}) {
			this.focus = date
			this.type = 'day'
		},

		getEventColor(event) {
			return event.color
		},

		setToday() {
			this.focus = ''
		},

		prev() {
			this.$refs.calendar.prev()
		},

		next() {
			this.$refs.calendar.next()
		},

		showEvent({nativeEvent, event}) {
			const open = () => {
				this.selectedEvent = event
				this.selectedElement = nativeEvent.target
				setTimeout(() => {
					this.selectedOpen = true
				}, 10)
			}

			if (this.selectedOpen) {
				this.selectedOpen = false
				setTimeout(open, 10)
			} else {
				open()
			}

			nativeEvent.stopPropagation()
		},

		updateRange({start, end}) {
			const events = []

			const min = new Date(`${start.date}T00:00:00`)
			const max = new Date(`${end.date}T23:59:59`)
			const days = (max.getTime() - min.getTime()) / 86400000
			const eventCount = this.rnd(days, days + 20)

			for (let i = 0; i < eventCount; i++) {
				const firstTimestamp = this.rnd(min.getTime(), max.getTime())
				const first = new Date(firstTimestamp - (firstTimestamp % 900000))
				const secondTimestamp = 30 * 60 * 1000
				const second = new Date(first.getTime() + secondTimestamp)

				events.push({
					name: this.names[this.rnd(0, this.names.length - 1)],
					start: first,
					end: second,
					color: this.colors[this.rnd(0, this.colors.length - 1)],
					timed: true,
				})
			}

			this.events = events
		},

		rnd(a, b) {
			return Math.floor((b - a + 1) * Math.random()) + a
		},
	},
}
</script>
