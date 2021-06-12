<template>
	<v-form>
		<v-container v-if="charon">
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.name"
								:counter="255"
								label="Task name"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>The name for this assignment. A category with this name will be created which will contain this assignment's grades.</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.project_folder"
								:counter="255"
								label="Project folder"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>The folder name for this assignment. Students have to put their code in this folder. This is not shown to students so it should be included in the task description.</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-select
								v-model="charon.tester_type_code"
								:items="testerTypes"
								item-text="name"
								item-value="code"
								hint="Tester type code"
								persistent-hint
								single-line
								v-bind="attrs"
								v-on="on"
							></v-select>
						</template>
						<span>Image used for testing</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.system_extra"
								:counter="255"
								label="System Extra (comma separated)"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>Additional parameters used for testing. See here for list of available options. Can be left empty.</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.tester_extra"
								:counter="255"
								label="Docker extra"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>Additional parameters used for testing. Value is sent to docker runner. Can be left empty.</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-slider
								v-model="charon.docker_timeout"
								color="purple"
								label="Docker timeout"
								min="0"
								max="3000"
								step="30"
								thumb-label="always"
								v-bind="attrs"
								v-on="on"
							></v-slider>
						</template>
						<span>Time after which docker gets killed. Default is 120 seconds.</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.docker_content_root"
								:counter="255"
								label="Docker content root"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>Where tester knows to look for exercise. Can be left empty.</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-text-field
								v-model="charon.docker_test_root"
								:counter="255"
								label="Docker test root"
								v-bind="attrs"
								v-on="on"
							></v-text-field>
						</template>
						<span>Where tester knows to look for student. Can be left empty.</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-menu
								v-model="defenseStartTimeMenu"
								:close-on-content-click="false"
								:nudge-right="40"
								transition="scale-transition"
								offset-y
								min-width="290px">
								<template v-slot:activator="{ on, attrs }">
									<v-text-field
										v-model="charon.defense_start_time"
										label="Student registration start time"
										prepend-icon="mdi-calendar"
										readonly
										v-bind="attrs"
										v-on="on"/>
								</template>
								<v-date-picker
									v-model="charon.defense_start_time"
									@input="defenseStartTimeMenu = false"/>
							</v-menu>
						</template>
						<span>Start time when given Charon will be available for student to register to</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-menu
								v-model="defenseDeadlineMenu"
								:close-on-content-click="false"
								:nudge-right="40"
								transition="scale-transition"
								offset-y
								min-width="290px">
								<template v-slot:activator="{ on, attrs }">
									<v-text-field
										v-model="charon.defense_deadline"
										label="Student registration deadline"
										prepend-icon="mdi-calendar"
										readonly
										v-bind="attrs"
										v-on="on"/>
								</template>
								<v-date-picker
									v-model="charon.defense_deadline"
									@input="defenseDeadlineMenu = false"/>
							</v-menu>
						</template>
						<span>Deadline when given Charon will no longer be available for student to register to</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-slider
								v-model="charon.group_size"
								color="purple"
								label="Group size"
								min="1"
								max="10"
								thumb-label="always"
								v-bind="attrs"
								v-on="on"
							></v-slider>
						</template>
						<span>Max size for group projects. Everyone gets the same grade</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-slider
								v-model="charon.defense_duration"
								color="purple"
								label="Duration"
								min="5"
								max="30"
								step="5"
								thumb-label="always"
								v-bind="attrs"
								v-on="on"
							></v-slider>
						</template>
						<span>Number of minutes that the defence will take place</span>
					</v-tooltip>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<v-slider
								v-model="charon.defense_threshold"
								color="purple"
								label="Threshold"
								min="0"
								max="100"
								thumb-label="always"
								v-bind="attrs"
								v-on="on"
							></v-slider>
						</template>
						<span>Minimum percentage that a student can register for defense with</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="6" lg="6">
					<v-switch
						dense
						v-model="charon.choose_teacher"
						label="Student can choose a teacher"
					></v-switch>
				</v-col>
			</v-row>
			
			<v-row>
				<v-col cols="9" sm="9" md="9" lg="9">
					<label>Labs</label>
					<v-tooltip top>
						<template v-slot:activator="{ on, attrs }">
							<multiselect v-model="charon.defense_labs" :options="filteredLabs"
										 :multiple="true"
										 label="name"
										 v-bind="attrs"
										 v-on="on"
										 :close-on-select="false" placeholder="Select labs" trackBy="id"
										 :clear-on-select="true" class="multiselect__width">
							</multiselect>
						</template>
						<span>Labs where this Charon can be defended</span>
					</v-tooltip>
				</v-col>
				
				<v-col cols="12" sm="6" md="2" lg="2">
					<p>Add all possible labs</p>
					<v-btn class="ma-2" tile outlined color="primary" @click="addAllLabs">
						Add all
					</v-btn>
				</v-col>
			</v-row>
		</v-container>
		
		<v-container v-else>
			<v-skeleton-loader
				type="list-item-three-line, divider, list-item-three-line, divider, list-item-three-line, divider, list-item-three-line"
			></v-skeleton-loader>
		</v-container>
	</v-form>
</template>

<script>

import Multiselect from "vue-multiselect";
import {Datepicker} from "../../../components/partials";
import Lab from "../../../api/Lab";
import Course from "../../../api/Course";

export default {
	name: "charon-settings-form",
	components: {Multiselect, Datepicker},

	props: {
		charon: {required: true},
		course_id: {required: true}
	},
	
	data() {
		return {
			defenseStartTimeMenu: false,
			defenseDeadlineMenu: false,
			labs: [],
			filteredLabs: [],
			testerTypes: []
		}
	},
	
	methods: {
		filterLabs() {
			const filtered_labs = [];
			
			for (let i = 0; i < this.labs.length; i++) {
				if (this.charon.defense_deadline == null || (new Date(this.charon.defense_deadline) >= new Date(this.labs[i].end))) {
					if (this.charon.defense_start_time == null || (new Date(this.charon.defense_start_time) <= new Date(this.labs[i].start))) {
						filtered_labs.push(this.labs[i])
					}
				}
			}
			
			this.filteredLabs = filtered_labs
		},
		
		addAllLabs() {
			this.charon.defense_labs = this.filteredLabs.slice()
		},
	},
	
	watch: {
		charon() {
			this.filterLabs()
		},
		
		defenseStartTimeMenu() {
			this.filterLabs()
		},
		
		defenseDeadlineMenu() {
			this.filterLabs()
		}
		
	},
	
	created() {
		Lab.all(this.course_id, labs => {
			this.labs = labs
		})
		
		Course.getTesterTypes(this.course_id, response => {
			this.testerTypes = response
		})
	}
}
</script>