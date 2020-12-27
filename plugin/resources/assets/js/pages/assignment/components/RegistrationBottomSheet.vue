<template>
	<!--Moodle drawer has z-index under 1000-->
	<v-bottom-sheet v-model="sheet" inset persistent style="position: relative; z-index: 1000">
		<template v-slot:activator="{ on, attrs }">
			<v-btn v-if="hasPoints && submissionStyleOK" v-bind="attrs" v-on="on" icon @click="sheet=true">
				<img alt="shield" height="24px" src="pix/shield.png" width="24px">
			</v-btn>
			
			<v-btn v-else icon @click="notify">
				<img alt="shield" height="24px" src="pix/shield.png" width="24px">
			</v-btn>
		</template>
		
		<div>
			<v-toolbar :color="color" dark>
				<span class="headline">{{ translate('registrationForText') }} {{ this.charon['name'] }}</span>
				
				<v-spacer></v-spacer>
				
				<v-btn color="error" @click="sheet = false">
					{{ translate('closeText') }}
				</v-btn>
			</v-toolbar>
			
			<v-sheet class="px-4 pt-4" height="80vh">
				<div class="register-lab-headers">
					<h4>{{ translate('chooseTeacherText') }}</h4>
				</div>
				
				<div class="labs-schedule">
					<div class="row">
						<div v-if="this.charon['choose_teacher'] === 1" class="col-6 col-sm-4"><label
							for="my-teacher"></label>
							<input id="my-teacher" v-model="selected" name="labs-time"
								   type="radio" value="My teacher" @click="changeTeacher('My teacher')">
							{{ translate('myTeacherText') }}
						</div>
						<div class="w-100 d-none d-md-block"></div>
						<div class="col-6 col-sm-4">
							<label for="another-teacher"></label>
							<input id="another-teacher" v-model="selected" name="labs-time" type="radio"
								   value="Any teacher" @click="changeTeacher('Any teacher')">
							{{ translate('anyTeacherText') }}
						</div>
					</div>
				</div>
				<div class="register-lab-headers" style="margin-top: 2vh">
					<h4>{{ translate('chooseTimeText') }}</h4>
				</div>
				<div class="labs-schedule">
					<div class="text-center">
						<multiselect v-model="value" :allow-empty="false" :block-keys="['Tab', 'Enter']"
									 :custom-label="getLabList" :max-height="200"
									 :options="this.labs" :placeholder="translate('selectDayText')"
									 label="start"
									 track-by="start" @select="onSelect">
							<template slot="singleLabel" slot-scope="{ option }">{{ option.start }}</template>
						</multiselect>
						
						<multiselect v-if="this.value != null" v-model="value_time" :max-height="200"
									 :options="this.times"
									 :placeholder="translate('selectTimeText')" style="margin-top: 30px">
						</multiselect>
					</div>
				</div>
				
				<v-row class="mt-4">
					<v-btn class="ml-4" color="primary" dense outlined text @click="sendData()">
						{{ translate('registerText') }}
					</v-btn>
					
					<v-btn class="ml-4" color="error" dense outlined text @click="sheet = false">
						{{ translate('closeText') }}
					</v-btn>
				</v-row>
			
			</v-sheet>
		</div>
	
	</v-bottom-sheet>

</template>

<script>
import {Multiselect} from "vue-multiselect";
import {Translate} from "../../../mixins";
import moment from "moment";
import {mapState} from "vuex";
import {getSubmissionWeightedScore} from "../helpers/submission";

export default {
	
	mixins: [Translate],
	
	components: {
		Multiselect
	},
	
	props: {
		submission: {required: true},
		color: {required: true}
	},
	
	name: "registration-bottom-sheet",
	
	data() {
		return {
			hasPoints: false,
			submissionStyleOK: true,
			sheet: false,
			value_time: null,
			times: [],
			not_available_times: [],
			cached_option: null,
			selected: 'Any teacher',
			value: null,
			teacher_options: [],
		}
	},
	
	computed: {
		...mapState([
			'charon_id',
			'student_id',
			'registrations',
			'charon',
			'labs'
		]),
	},
	
	
	methods: {
		getDefenseData() {
			axios.get(`api/charons/${this.charon_id}/registrations?id=${this.charon_id}&user_id=${this.student_id}`).then(result => {
				this.$store.state.registrations = result.data
			})
		},
		
		notify() {
			let submissionWeightedScore = getSubmissionWeightedScore(this.submission);
			
			if (!this.hasPoints) {
				VueEvent.$emit('show-notification', `You can't register a submission with a result ${submissionWeightedScore}%, as it's less than ${this.charon['defense_threshold']}%`, 'danger')
			}
			
			if (!this.submissionStyleOK) {
				VueEvent.$emit('show-notification', `Please fix your style before registering to submission`, 'danger')
			}
		},
		
		sendData() {
			if (this.value !== null && this.value['start'] !== null && this.value_time !== null && this.selected.length !== 0) {
				let chosen_time = this.value['start'].split(' ')[0] + " " + this.value_time;
				let selected_boolean = this.selected === "My teacher";
				
				axios.post(`api/charons/${this.charon.id}/submission?user_id=${this.student_id}`, {
					charon_id: this.charon.id,
					submission_id: this.submission.id,
					selected: selected_boolean,
					defense_lab_id: this.value['id'],
					student_chosen_time: chosen_time,
				}).then(() => {
					VueEvent.$emit('show-notification', "Registration was successful!", 'primary')
					this.isActive = false
				}).catch(error => {
					if (error.response && error.response.data && error.response.data.title) {
						VueEvent.$emit('show-notification', error.response.data.title + ' ' + error.response.data.detail, 'danger')
					} else {
						console.error(error);
						VueEvent.$emit('show-notification', 'Unexpected error, please try again', 'danger')
					}
				}).finally(() => {
					this.getDefenseData();
				})
			} else {
				VueEvent.$emit('show-notification', "Needed parameters weren't inserted!", 'danger')
			}
		},
		
		getLabList({start}) {
			let date = `${start.split(' ')[0]}`;
			let time = `${start.split(' ')[1]}`;
			let time_return = time.split(':');
			return date + " " + time_return[0] + ":" + time_return[1];
		},
		
		onSelect(option) {
			this.cached_option = option;
			this.arrayDefenseTime();
		},
		
		changeTeacher(teacher) {
			this.selected = teacher;
			this.arrayDefenseTime();
		},
		
		arrayDefenseTime() {
			if (this.cached_option != null) {
				const option = this.cached_option;
				this.times.length = 0;
				let time = option['start'].split(' ')[0];
				this.timeGenerator(option);
				axios.get(`api/charons/${this.charon.id}/labs/unavailable?time=${time
					}&my_teacher=${this.selected === "My teacher"
					}&user_id=${this.student_id
					}&lab_id=${option['id']
					}&charon_id=${this.charon.id}`
				).then(result => {
					this.not_available_times = result.data;
					this.timeGenerator(option);
				})
			}
		},
		
		timeGenerator(option) {
			let defense_duration = this.charon['defense_duration'];
			let startTime = moment(option['start'], 'YYYY-MM-DD HH:mm:ii')
			let endTime = moment(option['end'], 'YYYY-MM-DD HH:mm:ii');
			let curTime = moment();
			
			this.times = [];
			while (startTime < endTime) {
				const time = startTime.format('HH:mm');
				if (!this.not_available_times.includes(time) && curTime.isBefore(startTime)) {
					this.times.push(time);
				}
				startTime.add(defense_duration, 'minutes');
			}
			if (this.not_available_times.length !== 0) {
				this.times = this.times.filter(x => !this.not_available_times.includes(x));
			}
		},
	},
	
	created() {
		if (this.charon['choose_teacher'] === 1) {
			this.teacher_options = ["Any teacher"]
		} else {
			this.teacher_options = ["My teacher", "Any teacher"]
		}
		
		this.submissionStyleOK = true
		for (let j = 0; j < this.submission.results.length; j++) {
			const code = parseInt(this.submission.results[j].grade_type_code);
			if (code > 100 && code <= 1000) {
				const result = parseFloat(this.submission.results[j].calculated_result);
				if (result < 0.999) {
					this.submissionStyleOK = false
				}
			}
		}
		
		this.hasPoints = getSubmissionWeightedScore(this.submission) >= this.charon['defense_threshold'];
	}
}
</script>
