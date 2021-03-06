<template>
	<v-bottom-sheet v-model="sheet" inset style="position: relative; z-index: 1000">
		<template v-slot:activator="{ on, attrs }">
			<v-btn class="mt-4 ml-4" v-bind="attrs" v-on="on" color="primary" outlined dense text @click="sheet = true">
				{{ translate('myRegistrationsText') }}
			</v-btn>
		</template>
		
		<div>
			<v-toolbar color="success" dark>
				<span class="headline">{{ translate('allRegistrationsText') }}</span>
				
				<v-spacer></v-spacer>
				
				<v-btn color="error" @click="sheet = false">
					{{ translate('closeText') }}
				</v-btn>
			</v-toolbar>
			
			<v-sheet height="80vh" class="pt-4 px-4">
				<student-registrations/>
				
				<v-row class="my-4">
					<v-btn class="mt-4 ml-4" outlined text color="error" @click="sheet = false">
						{{ translate('closeText') }}
					</v-btn>
				</v-row>
			</v-sheet>
		</div>
	
	</v-bottom-sheet>
</template>

<script>
import {Translate} from "../../../mixins";
import StudentRegistrations from "./StudentRegistrations";

export default {
	name: "student-registration-sheet",
	
	mixins: [Translate],
	
	components: {
		StudentRegistrations
	},
	
	data() {
		return {
			sheet: false
		};
	},
	
}
</script>
