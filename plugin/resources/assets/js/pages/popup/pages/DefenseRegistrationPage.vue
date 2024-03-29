<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Registrations</v-card-title>
        </v-card>
        <popup-section
                title="Code showing registrations"
                subtitle="Here are all the registrations for code showing. Select your name in the 'Teacher name' and press start session - then progress will be automatically updated"
        >
            <template slot="header-right">
                <v-btn class="ma-2" tile outlined color="primary" dense @click="AddRegistration">
                    Add registration
                </v-btn>
            </template>
                <v-card class="mx-auto" outlined light raised>
                    <v-container class="spacing-playground pa-3" fluid>
                        <v-row>

                            <v-col cols="12" xs="12" sm="12" md="6" lg="3">
                                <div class="helper">
                                    After
                                </div>
                                <div class="datepick">
                                    <datepicker :datetime="after"></datepicker>
                                    <input type="hidden" :value="after">
                                </div>
                            </v-col>

                            <v-col cols="12" xs="12" sm="12" md="6" lg="3">
                                <div class="helper">
                                    Before
                                </div>
                                <div class="datepick">
                                    <datepicker :datetime="before"></datepicker>
                                    <input type="hidden" :value="before">
                                </div>
                            </v-col>

                            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
                                <div class="helper">
                                    Teacher name
                                </div>

                                <v-select
                                        :disabled="isSessionActive"
                                        class="mx-auto"
                                        dense
                                        single-line
                                        item-text="fullname"
                                        item-value="id"
                                        :items="teachers"
                                        v-model="filter_teacher"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
                                <div class="helper">
                                    Progress
                                </div>

                                <v-select
                                        class="mx-auto"
                                        dense
                                        :items="all_progress_types"
                                        v-model="filter_progress"
                                ></v-select>
                            </v-col>

                            <v-col cols="12" xs="12" sm="4" md="4" lg="2">
                                <v-btn class="ma-2" tile outlined color="primary" dense @click="apply">
                                    Apply
                                </v-btn>

                                <v-btn class="ma-2" tile outlined color="error" dense @click="endSession"
                                       v-if="isSessionActive">
                                    End session
                                </v-btn>

                                <v-btn class="ma-2" tile outlined color="primary" dense @click="startSession" v-else>
                                    Start session
                                </v-btn>
                            </v-col>

                        </v-row>
                    </v-container>
                </v-card>

            <defense-registrations-section :defense-list="defenseList" :teachers="teachers"/>
        </popup-section>
    </div>

</template>

<script>

    import {mapState, mapActions, mapGetters} from "vuex";

    import {PopupSection} from '../layouts/index'
    import DefenseRegistrationsSection from "../sections/DefenseRegistrationsSection";
    import Defense from "../../../api/Defense";
    import Teacher from "../../../api/Teacher";
    import moment from "moment";
    import Datepicker from "../../../components/partials/Datepicker";
    import router from "../routes";

    export default {
        name: "defense-registrations-page",
        components: {Datepicker, DefenseRegistrationsSection, PopupSection},
        data() {
            return {
                all_progress_types: ['Waiting', 'Defending', 'Done'],
                after: {time: `${moment().format("YYYY-MM-DD")} 00:00`},
                before: {time: null},
                filter_teacher: -1,
                filter_progress: null,
                defenseList: [],
                countDown: 0,
                teachers: []
            }
        },
        computed: {

            ...mapState([
                'teacher', 'course'
            ]),

            isSessionActive() {
                return this.teacher != null
            }
        },

        created() {
            this.fetchRegistrations()
            VueEvent.$on('refresh-page', this.fetchRegistrations)
            Teacher.getAllTeachers(this.course.id, response => {
                this.teachers = response
            })
        },

        beforeDestroy() {
            VueEvent.$off('refresh-page', this.fetchRegistrations)
        },

        methods: {
            ...mapActions(["updateTeacher"]),

            apply() {
                Defense.filtered(this.course.id, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
                    this.defenseList = response
                })
            },

            startSession() {
                const teacher_id = this.findWithAttr(this.teachers, 'id', this.filter_teacher)

                if (teacher_id > -1) {
                    const teacher = this.teachers[teacher_id]
                    this.updateTeacher({teacher})
                    this.apply()
                    VueEvent.$emit('show-notification', "Session started", 'danger')
                } else {
                    VueEvent.$emit('show-notification', "Please select a teacher", 'danger')
                }
            },

            endSession() {
                const teacher = null
                this.updateTeacher({teacher})
                VueEvent.$emit('show-notification', "Session ended", 'danger')
            },

            findWithAttr(array, attr, value) {
                for (let i = 0; i < array.length; i += 1) {
                    if (array[i][attr] === value) {
                        return i;
                    }
                }
                return -1;
            },

            fetchRegistrations() {
                Defense.filtered(this.course.id, this.after.time, this.before.time, this.filter_teacher, this.filter_progress, response => {
                    this.defenseList = response
                })
            },
            AddRegistration(){
                router.push(`addRegistration`)
            }
        }
    }
</script>

<style>
    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 10px 10px !important;
    }
</style>
