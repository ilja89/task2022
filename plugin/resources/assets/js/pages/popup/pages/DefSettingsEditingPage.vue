<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Edit Charon settings</v-card-title>
        </v-card>

        <popup-section title="Charon Settings"
                       subtitle="Here are the general settings for each charon.">
            <v-card class="mx-auto" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>
                    <v-form>
                        <v-container>
                            <v-row>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-select
                                            v-model="charon.tester_type_code"
                                            :items="testerTypes"
                                            item-text="name"
                                            item-value="code"
                                            hint="Tester type code"
                                            persistent-hint
                                            single-line
                                    ></v-select>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-text-field
                                            v-model="charon.system_extra"
                                            :counter="255"
                                            label="System Extra (comma separated)"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-text-field
                                            v-model="charon.tester_extra"
                                            :counter="255"
                                            label="Docker extra"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <p>Docker timeout (seconds)</p>
                                    <v-slider
                                            v-model="charon.docker_timeout"
                                            color="purple"
                                            label="Docker timeout"
                                            min="0"
                                            max="3000"
                                            step="30"
                                            thumb-label
                                    ></v-slider>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-text-field
                                            v-model="charon.docker_content_root"
                                            :counter="255"
                                            label="Docker content root (set this if you know what you're doing)"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-text-field
                                            v-model="charon.docker_test_root"
                                            :counter="255"
                                            label="Docker test root (set this if you know what you're doing)"
                                    ></v-text-field>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <div>
                                        <p class="input-helper">Defense start time</p>
                                        <datepicker :datetime="charon.defense_start_time"></datepicker>
                                        <input type="hidden" :value="charon.defense_start_time">
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <div>
                                        <p class="input-helper">Defense deadline</p>
                                        <datepicker :datetime="charon.defense_deadline"></datepicker>
                                        <input type="hidden" :value="charon.defense_deadline">
                                    </div>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <p>Group size (1 is individual, more is group project)</p>
                                    <v-slider
                                            v-model="charon.group_size"
                                            color="purple"
                                            label="Group size"
                                            min="1"
                                            max="10"
                                            thumb-label
                                    ></v-slider>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <p>Minutes defence takes place</p>
                                    <v-slider
                                            v-model="charon.defense_duration"
                                            color="purple"
                                            label="Duration"
                                            min="1"
                                            max="30"
                                            thumb-label
                                    ></v-slider>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <p>Minimum percentage to register for defense</p>
                                    <v-slider
                                            v-model="charon.defense_threshold"
                                            color="purple"
                                            label="Threshold"
                                            min="0"
                                            max="100"
                                            thumb-label
                                    ></v-slider>
                                </v-col>

                                <v-col cols="12" sm="6" md="6" lg="6">
                                    <v-container class="px-0" fluid>
                                        <v-switch
                                                v-model="charon.choose_teacher"
                                                label="Student can choose a teacher"
                                        ></v-switch>
                                    </v-container>
                                </v-col>

                                <v-col cols="12" sm="12" md="8" lg="8">
                                    <label>Labs</label>
                                    <p>Labs where this Charon can be defended</p>
                                    <multiselect v-model="charon.charonDefenseLabs" :options="filtered_labs"
                                                 :multiple="true"
                                                 label="name"
                                                 :close-on-select="false" placeholder="Select labs" trackBy="id"
                                                 :clear-on-select="true" class="multiselect__width">
                                    </multiselect>
                                </v-col>

                                <v-col cols="12" sm="6" md="2" lg="2">
                                    <p>Recalculate labs</p>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="filterLabs">
                                        Recalculate
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" sm="6" md="2" lg="2">
                                    <p>Add all possible labs</p>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="addAllLabs">
                                        Add all
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-form>

                    <v-btn class="ma-2" small tile outlined color="primary"
                           @click="saveClicked">
                        Save
                    </v-btn>

                    <v-btn class="ma-2" small tile outlined color="error" @click="cancelClicked">
                        Cancel
                    </v-btn>

                </v-container>
            </v-card>
        </popup-section>
    </div>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import {mapState} from "vuex";
    import Lab from "../../../api/Lab";
    import Charon from "../../../api/Charon";
    import {Datepicker} from "../../../components/partials";
    import Multiselect from "vue-multiselect";
    import Course from "../../../api/Course";

    export default {
        name: "charon-settings-editing-page",
        components: {Datepicker, Multiselect, PopupSection},
        data() {
            return {
                labs: [],
                show_info: true,
                filtered_labs: [],
                testerTypes: []
            }
        },
        methods: {
            filterLabs() {
                const filtered_labs = [];

                for (let i = 0; i < this.labs.length; i++) {
                    if (this.charon.defense_deadline.time == null || (new Date(this.charon.defense_deadline.time) >= new Date(this.labs[i].end))) {
                        if (this.charon.defense_start_time.time == null || (new Date(this.charon.defense_start_time.time) <= new Date(this.labs[i].start))) {
                            filtered_labs.push(this.labs[i])
                        }
                    }
                }
                this.filtered_labs = filtered_labs
            },

            addAllLabs() {
                this.charon.charonDefenseLabs = this.filtered_labs.slice()
            },

            formatTime: function (give_deadline) {
                if (give_deadline.toString().includes('GMT')) {
                    let num = give_deadline.toString().substring(give_deadline.toString().indexOf('GMT') + 4,
                        give_deadline.toString().indexOf('GMT') + 6)
                    if (give_deadline.toString().includes('GMT+')) {
                        give_deadline = new Date(give_deadline.setHours(give_deadline.getHours() + parseInt(num)))
                    }
                    if (give_deadline.toString().includes('GMT-')) {
                        give_deadline = new Date(give_deadline.setHours(give_deadline.getHours() - parseInt(num)))
                    }
                }
                return give_deadline;
            },

            saveClicked() {
                try {
                    let chosen_labs = []
                    for (let i = 0; i < this.charon.charonDefenseLabs.length; i++) {
                        chosen_labs.push(this.charon.charonDefenseLabs[i].id)
                    }
                    let give_start_time = this.formatTime(this.charon.defense_start_time.time);
                    let give_deadline = this.formatTime(this.charon.defense_deadline.time);

                    Charon.saveCharon(this.charon, give_start_time, give_deadline, chosen_labs, () => {
                        window.location = "popup#/charonSettings";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Charon settings successfully updated!')
                    })
                } catch (e) {
                    VueEvent.$emit('show-notification', 'Make sure start time and deadline are filled!')
                }
            },

            getNamesForLabs() {
                for (let i = 0; i < this.labs.length; i++) {
                    this.labs[i].name = this.getDayTimeFormat(new Date(this.labs[i].start))
                        + ' (' + this.getNiceDate(new Date(this.labs[i].start)) + ')'
                }
            },

            getDayTimeFormat(start) {
                let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                return daysDict[start.getDay()] + start.getHours();
            },

            getNiceDate(date) {
                let month = (date.getMonth() + 1).toString();
                if (month.length === 1) {
                    month = "0" + month
                }
                return date.getDate() + '.' + month + '.' + date.getFullYear()
            },

            cancelClicked() {
                window.location = "popup#/charonSettings";
            },
        },
        computed: {

            ...mapState([
                'charon',
                'course'
            ]),
        },

        created() {
            Lab.all(this.course.id, labs => {
                this.labs = labs
                this.getNamesForLabs()
                this.filterLabs()
            })

            Course.getTesterTypes(this.course.id, response => {
                this.testerTypes = response
            })
        }
    }
</script>

<style>
    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 10px 10px;
    }
</style>