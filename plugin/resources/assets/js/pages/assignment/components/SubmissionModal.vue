<template>
	<v-dialog v-model="isActive" width="80%" style="position: relative; z-index: 3000"
			  transition="dialog-bottom-transition">
		<template v-slot:activator="{ on, attrs }">
			<v-btn icon @click="isActive=true" v-bind="attrs" v-on="on">
				<img alt="eye" height="24px" src="pix/eye.png" width="24px">
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
					<submission-table :submission="submission"></submission-table>
				</div>
				
				<h3>{{ translate('filesText') }}</h3>
				
				<files-component-without-tree :submission="submission" :testerType="testerType" :isRound="true">
				</files-component-without-tree>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>

<script>
import {FilesComponentWithoutTree} from '../../../components/partials'
import {Translate} from '../../../mixins'
import SubmissionTable from "./SubmissionTable";

export default {
	name: "submission-modal",
	
	mixins: [Translate],
	
	components: {FilesComponentWithoutTree, SubmissionTable},
	
	props: {
		submission: {required: true},
		color: {required: true}
	},
	
	data() {
		return {
			isActive: false,
			testerType: '',
			toggleOn: false
		}
	},
	
	computed: {
		hasCommitMessage() {
			return this.submission.git_commit_message !== null && this.submission.git_commit_message.length > 0
		},
		
		hasMail() {
			return this.submission.mail !== null && this.submission.mail.length > 0
		},
	},
	
	mounted() {
		this.testerType = window.testerType
	},
}
</script>
<style scoped>
@import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
@import url("https://fonts.googleapis.com/css?family=Material+Icons");

/* The switch - the box around the slider */
.switch {
	position: relative;
	display: inline-block;
	width: 60px;
	height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
	opacity: 0;
	width: 0;
	height: 0;
}

/* The slider */
.slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	-webkit-transition: .4s;
	transition: .4s;
}

.slider:before {
	position: absolute;
	content: "";
	height: 26px;
	width: 26px;
	left: 4px;
	bottom: 4px;
	background-color: white;
	-webkit-transition: .4s;
	transition: .4s;
}

input:checked + .slider {
	background-color: #2196F3;
}

input:focus + .slider {
	box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
	-webkit-transform: translateX(26px);
	-ms-transform: translateX(26px);
	transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
	border-radius: 34px;
}

.slider.round:before {
	border-radius: 50%;
}
</style>