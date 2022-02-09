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

                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <div class="labs-field">
                                        <p>
                                            <span style="margin-right: 1rem">Lab duration:</span>
                                            <span class="headline" style="font-weight: lighter" v-text="labDuration"></span>
                                            <span style="margin-left: 0.2rem">minutes</span>
                                            <span class="subtitle-2" style="margin-left: 0.5rem">or</span>
                                            <span class="headline" style="margin-left: 0.5rem; font-weight: lighter" v-text="stylizedLabDuration()"></span>
                                        </p>
                                        <v-slider
                                            v-model="labDuration"
                                            track-color="grey"
                                            thumb-label
                                            always-dirty
                                            min="5"
                                            max="180"
                                            step="5"
                                            @change="() => {this.assignDates(this.labDuration)}"
                                        >
                                        </v-slider>
                                    </div>
                                </v-col>
                                <v-col cols="12" sm="12" md="4" lg="4">
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(30)}">
                                        30 mins
                                    </v-btn>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(60)}">
                                        1 hour
                                    </v-btn>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(90)}">
                                        1.5 hour
                                    </v-btn>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(120)}">
                                        2 hours
                                    </v-btn>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(150)}">
                                      2.5 hour
                                    </v-btn>
                                    <v-btn class="ma-2" tile outlined color="primary" @click="() => {timeButtonClicked(180)}">
                                        3 hours
                                    </v-btn>
                                </v-col>

                                <v-col cols="12" sm="12" md="4" lg="4">

                                    Registration type

                                    <div>
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                    outlined
                                                    @click="registrationToggle('everyone')"
                                                    :class="[checkRegistrationType('everyone') ? 'grp-type-btn-active' : '' ]"
                                                    v-bind="attrs"
                                                    v-on="on"
                                                >
                                                    Single
                                                </v-btn>
                                            </template>
                                            <span>Every student is allowed to register</span>
                                        </v-tooltip>
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                    outlined
                                                    @click="registrationToggle('group')"
                                                    :class="[checkRegistrationType('group') ? 'grp-type-btn-active' : '' ]"
                                                    v-bind="attrs"
                                                    v-on="on"
                                                >
                                                    Group
                                                </v-btn>
                                            </template>
                                            <span>Every student from group is allowed to register</span>
                                        </v-tooltip>
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                    outlined
                                                    @click="registrationToggle('teams')"
                                                    :class="[checkRegistrationType('teams') ? 'grp-type-btn-active' : '' ]"
                                                    v-bind="attrs"
                                                    v-on="on"
                                                >
                                                    Teams
                                                </v-btn>
                                            </template>
                                            <span>Registration is per team, team need to be in grouping</span>
                                        </v-tooltip>
                                    </div>
                                    <add-groups-selector v-if="checkRegistrationType('group')"
                                                         :lab="lab" :courseGroups="courseGroups"
                                                         :courseGroupings="courseGroupings"
                                    ></add-groups-selector>
                                    <add-groupings-selector v-else-if="checkRegistrationType('teams')"
                                                            :lab="lab"
                                                            :courseGroupings="courseGroupings"
                                    ></add-groupings-selector>
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
                                        <multiselect v-model="lab.charons" :options="filteredCharons" :multiple="true"
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

            <div class="lab-details-message">
                <div v-if="registrations === -1">
                    Checking for active registrations ...
                </div>
                <div v-else-if="registrations > 0">
                    {{registrations}} active registrations would be lost with this change
                </div>
            </div>

            <v-btn class="ma-2" tile outlined color="primary" @click="saveClicked">
                <span v-if="registrations <= 0">Save</span>
                <span v-else>Confirm</span>
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
    import AddGroupingsSelector from "./sections/AddGroupingsSelector";
    import AddGroupsSelector from "./sections/AddGroupsSelector";
    import {mapState} from "vuex";
    import Lab from "../../../../api/Lab";
    import Teacher from "../../../../api/Teacher";
    import Charon from "../../../../api/Charon";
    import {Datepicker} from "../../../../components/partials";
    import {CharonSelect} from "../../../../components/form";
    import Multiselect from "vue-multiselect";
    import CharonFormat from "../../../../helpers/CharonFormat";
    import _ from "lodash";

    export default {

        components: {Datepicker, CharonSelect, Multiselect, AddMultipleLabsSection, AddGroupsSelector, PopupSection, AddGroupingsSelector},

        data() {
            return {
                charons: [],
                teachers: [],
                show_info: true,
                filteredCharons: [],
                labDuration: 0,
                registrations: 0,
                registrationType: 'everyone',
                courseGroups: [],
                courseGroupings: [],
            }
        },

        methods: {
            filterCharons() {
                var filteredCharons = []
                for (let i = 0; i < this.charons.length; i++) {
                    if (this.charons[i].defense_deadline == null || (new Date(this.charons[i].defense_deadline) >= new Date(this.lab.end.time))) { // .time is because vue-datepicker made it so.
                        if (this.charons[i].defense_start_time == null || (new Date(this.charons[i].defense_start_time) <= new Date(this.lab.start.time))) {
                            filteredCharons.push(this.charons[i])
                        }
                    }
                }
                this.filteredCharons = filteredCharons
            },

            addAllCharons() {
                this.lab.charons = this.filteredCharons.slice()
            },

            saveClicked() {
                if (!this.lab.start.time || !this.lab.end.time) {
                    VueEvent.$emit('show-notification', 'Please fill all the required fields.', 'danger');
                    return
                }

                let chosenTeachers = []
                if (this.lab.teachers !== undefined) {
                    for (let i = 0; i < this.lab.teachers.length; i++) {
                        chosenTeachers.push(this.lab.teachers[i].id)
                    }
                }

                let chosenCharons = []
                if (this.lab.charons !== undefined) {
                    for (let i = 0; i < this.lab.charons.length; i++) {
                        chosenCharons.push(this.lab.charons[i].id)
                    }
                }

                if (!this.lab.name) {
                    this.lab.name = this.namePlaceholder;
                }

                let groups;

                if (this.registrationType === "teams") {
                    groups = [];
                    console.log(this.lab.groupings);
                    this.lab.groupings.forEach(grouping => grouping.groups.forEach( function (group) {
                        if (!groups.find( (g) => { return g.id == group.id; } )) {
                            groups.push(group.id);
                        }
                    }));
                } else {
                    groups = _.map(this.lab.groups, "id");
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

                    let filter = this.detectChanges(this.labInitial, chosenCharons, chosenTeachers);

                    // (registrations > 0) means that lost registrations 
                    // are already fetched for current lab and shown to user.
                    // Second click to Save confirms update on this case.
                    if (_.isEmpty(filter) || (this.registrations > 0)) {
                        this.updateLab(giveStart, giveEnd, chosenTeachers, chosenCharons, groups);
                    } else {
                        this.registrations = -1;
                        Lab.checkRegistrations(this.course.id, this.lab.id, filter, (result) => {
                            this.registrations = result;
                            if (result === 0) {
                                this.updateLab(giveStart, giveEnd, chosenTeachers, chosenCharons, groups);
                            }
                        });
                    }
                } else {
                    Lab.save(this.course.id, this.lab.start.time, this.lab.end.time, this.lab.name, chosenTeachers,
                        chosenCharons, groups, this.registrationType, this.lab.weeks, () => {
                        window.location = "popup#/labs";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Lab saved!');
                    })
                }
            },
            cancelClicked() {
                window.location = "popup#/labs";
            },
            stylizedLabDuration() {
                const mins = this.labDuration % 60;
                const hours = Math.floor(this.labDuration / 60)
                return `${hours}h${mins}min`;
            },
            assignDates(offset) {
              if (this.lab.start.time === null) {
                VueEvent.$emit('show-notification', 'You need to pick a start time first.');
                return
              }
              const currentDate = new Date(this.lab.start.time)
              const endDate = new Date(currentDate.getTime() + offset * 60000);

              this.lab.end = {time: this.assembleLabDate(endDate)};
            },
            timeButtonClicked(time) {
                this.labDuration = time;
                this.assignDates(time)
            },
            assembleLabDate(dateObj) {
                return dateObj.getFullYear() + '-' +
                    ('0' + (dateObj.getMonth()+1)).slice(-2) + '-' +
                    ('0' + dateObj.getDate()).slice(-2) + ' ' +
                    ('0' + dateObj.getHours()).slice(-2) + ':' +
                    ('0' + dateObj.getMinutes()).slice(-2)
            },

            detectChanges(old, charons, teachers) {
                let filter = {};

                let missing = [];
                for (let ch of old.charons) {
                    if (!charons.includes(ch.id)) {
                        missing.push(ch.id);
                    }
                }
                if (missing.length) {
                    filter.charons = [...missing];
                }

                missing = [];
                for (let tc of old.teachers) {
                    if (!teachers.includes(tc.id)) {
                        missing.push(tc.id);
                    }
                }
                if (missing.length) {
                    filter.teachers = [...missing];
                }

                return filter;
            },

            registrationToggle(type) {
                this.registrationType = type;
            },

            checkRegistrationType(type) {
                return this.registrationType === type;
            },

            updateLab(giveStart, giveEnd, chosenTeachers, chosenCharons, groups){
                Lab.update(this.course.id, this.lab.id, giveStart, giveEnd, this.lab.name, chosenTeachers,
                    chosenCharons, groups, this.registrationType, () => {
                    window.location = "popup#/labs";
                    window.location.reload();
                    VueEvent.$emit('show-notification', 'Lab updated!');
                });
            }
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
                return CharonFormat.getDayTimeFormat(date) + ' (' + CharonFormat.getNiceDate(date) + ')'
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

            Lab.getGroups(this.course.id, (response) => {
                this.courseGroups = response["groups"] || [];
                this.courseGroupings = response["groupings"] || [];
            });

            this.labInitial = _.cloneDeep(this.lab);
        },

        watch: {
            lab: {
                deep: true,
                handler() {
                    this.registrations = 0;
                    if (this.lab.id != this.labInitial.id) {
                        this.labInitial = _.cloneDeep(this.lab);
                    }
                }
            },

            getRegistrationType() {
                this.registrationType = this.lab.groups.length > 0 ? 'group':
                    this.lab.groupings.length > 0 ? 'teams' : 'everyone'
            }
        }

    }
</script>

<style src="../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

    .lab-details-message div {
        margin-left: 10px;
        color: red;
    }

    .grp-type-btn-active {
      color: #16d059 !important;
    }

</style>
