<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Lab settings</v-card-title>
        </v-card>

        <popup-section title="Edit Lab Settings"
                       subtitle="Here are the specifics for each Charon.">

            <v-card class="mx-auto mb-16" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>
                    <v-form>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <div class="labs-field">
                                        <p>Start date and time</p>
                                        <datepicker :datetime="lab.start"></datepicker>
                                        <input type="hidden" :value="lab.start">
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <div class="labs-field">
                                        <p>End time</p>
                                        <datepicker :datetime="lab.end"></datepicker>
                                        <input type="hidden" :value="lab.end">
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <div class="labs-field">
                                        <p>Name</p>
                                        <input v-model="lab.name" type="text" class="input" :placeholder="namePlaceholder">
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="12" md="12" lg="12">
                                    <div class="labs-field is-flex-1">
                                        <p>Teachers attending this lab session</p>
                                        <multiselect v-model="lab.teachers" :options="teachers" :multiple="true"
                                                     label="fullname"
                                                     :close-on-select="false" placeholder="Select teachers" trackBy="id"
                                                     :clear-on-select="true">
                                        </multiselect>
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="12" md="12" lg="8">
                                    <div class="labs-field is-flex-1">
                                        <p>Defendable Charons during this lab</p>
                                        <multiselect v-model="lab.charons" :options="filtered_charons" :multiple="true"
                                                     label="project_folder"
                                                     :close-on-select="false" placeholder="Select charons" trackBy="id"
                                                     :clear-on-select="true">
                                        </multiselect>
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="12" md="6" lg="2">
                                    <p>Recalculate labs</p>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="filterCharons">
                                        Recalculate
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" sm="12" md="6" lg="2">
                                    <div class="labs-field">
                                        <p>Add all possible charons</p>
                                        <v-btn class="ma-2" tile outlined color="primary" @click="addAllCharons">
                                            Add all
                                        </v-btn>
                                    </div>
                                </v-col>

                            </v-row>
                        </v-container>
                    </v-form>

                </v-container>
            </v-card>

            <add-multiple-labs-section :lab="lab"></add-multiple-labs-section>

            <v-btn class="ma-2" tile outlined color="primary" @click="saveClicked">
                Save
            </v-btn>

            <v-btn class="ma-2" tile outlined color="error" @click="cancelClicked">
                Cancel
            </v-btn>
        </popup-section>
    </div>
</template>

<script>
    import {PopupSection} from '../../layouts/index'
    import AddMultipleLabsSection from "./sections/AddMultipleLabsSection";
    import {mapState} from "vuex";
    import Lab from "../../../../api/Lab";
    import Teacher from "../../../../api/Teacher";
    import Charon from "../../../../api/Charon";
    import {Datepicker} from "../../../../components/partials";
    import {CharonSelect} from "../../../../components/form";
    import Multiselect from "vue-multiselect";
    import CharonFormat from "../../../../helpers/CharonFormat";

    export default {

        components: {Datepicker, CharonSelect, Multiselect, AddMultipleLabsSection, PopupSection},

        data() {
            return {
                charons: [],
                teachers: [],
                show_info: true,
                filtered_charons: []
            }
        },

        methods: {
            filterCharons() {
                var filtered_charons = []
                for (let i = 0; i < this.charons.length; i++) {
                    if (this.charons[i].defense_deadline == null || (new Date(this.charons[i].defense_deadline) >= new Date(this.lab.end.time))) { // .time is because vue-datepicker made it so.
                        if (this.charons[i].defense_start_time == null || (new Date(this.charons[i].defense_start_time) <= new Date(this.lab.start.time))) {
                            filtered_charons.push(this.charons[i])
                        }
                    }
                }
                this.filtered_charons = filtered_charons
            },

            addAllCharons() {
                this.lab.charons = this.filtered_charons.slice()
            },

            saveClicked() {
                if (!this.lab.start.time || !this.lab.end.time) {
                    VueEvent.$emit('show-notification', 'Please fill all the required fields.', 'danger');
                    return
                }

                let chosen_teachers = []
                if (this.lab.teachers !== undefined) {
                    for (let i = 0; i < this.lab.teachers.length; i++) {
                        chosen_teachers.push(this.lab.teachers[i].id)
                    }
                }

                let chosen_charons = []
                if (this.lab.charons !== undefined) {
                    for (let i = 0; i < this.lab.charons.length; i++) {
                        chosen_charons.push(this.lab.charons[i].id)
                    }
                }

                if (this.lab.id != null) {
                    let giveStart = this.lab.start.time
                    let giveEnd = this.lab.end.time

                    if (giveStart instanceof Date) {
                        giveStart = giveStart.toString().slice(0, 24)
                    }

                    if (giveEnd instanceof Date) {
                        giveEnd = giveEnd.toString().slice(0, 24)
                    }

                    Lab.update(this.course.id, this.lab.id, giveStart, giveEnd, this.lab.name, chosen_teachers, chosen_charons, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab updated!');
                    })
                } else {
                    Lab.save(this.course.id, this.lab.start.time, this.lab.end.time, this.lab.name, chosen_teachers, chosen_charons, this.lab.weeks, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab saved!');
                    })
                }
            },
            cancelClicked() {
                window.location = "popup#/labs";
            },
        },
        computed: {
            ...mapState([
                'lab',
                'course'
            ]),

            isEditing() {
                return window.isEditing;
            },
            namePlaceholder() {
                const date = this.lab && this.lab.start && this.lab.start.time ? new Date(this.lab.start.time) : new Date();
                return CharonFormat.getDayTimeFormat(date);
            }
        },

        created() {
            Teacher.getAllTeachers(this.course.id, (response) => {
                this.teachers = response;
            })

            Charon.all(this.course.id, (charons) => {
                this.charons = charons
                this.filterCharons()
            })
        }
    }
</script>

<style src="../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

</style>
