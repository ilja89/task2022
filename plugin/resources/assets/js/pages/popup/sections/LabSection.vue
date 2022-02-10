<template>
    <popup-section
            title="Labs overview"
            subtitle="Here are the the labs where students can show their code.">
        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" v-on:click="addNewLabSessionClicked">Add new</v-btn>
        </template>

        <alert-box-component v-if="alert"
                             :eventName="'delete-lab'"
                             :question="'Are you sure you want to delete the lab?'"
                             :text="labDeleteText"
                             :buttonNames="deleteLabButtonNames">
        </alert-box-component>

        <v-card-title v-if="labs.length">
            Labs
            <v-spacer></v-spacer>
            <div class="subtitle-1">
                Start date &nbsp;&nbsp;
            </div>
            <div class="subtitle-1">
                <datepicker :datetime="start_date" :date_only=true></datepicker>
            </div>
            <v-spacer></v-spacer>
            <v-text-field
                    v-if="labs.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
            </v-text-field>
        </v-card-title>

        <v-card-title v-else>
            No Labs for this course!
        </v-card-title>

        <v-data-table
                id="lab-overview-headers"
                v-if="labs.length"
                :headers="labs_headers"
                :items="labs_table"
                :search="search">

            <template v-slot:item.actions="{ item }">
                <v-btn class="ma-2" small tile outlined color="primary" @click="editLabClicked(item)">Edit
                </v-btn>
                <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
                    Delete
                </v-btn>
            </template>
            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>
        </v-data-table>

    </popup-section>
</template>

<style lang="scss">
    #lab-overview-headers {
        .v-data-table__mobile-row {
            min-height: 48px;
            height: auto;
        }
    }
    .lab-overview-message div {
        margin-left: 16px;
        &.green-text {
            color: green;
        }
    }
</style>

<script>
    import {PopupSection} from '../layouts/index'
    import {mapActions, mapState} from "vuex";
    import Lab from "../../../api/Lab";
    import CharonFormat from "../../../helpers/CharonFormat";
    import moment from "moment";
    import Datepicker from "../../../components/partials/Datepicker";
    import _ from "lodash";
    import AlertBoxComponent from "../../../components/partials/AlertBoxComponent";

    export default {
        name: "lab-section",

        components: {PopupSection, Datepicker, AlertBoxComponent},

        props: {
            labs: {required: true}
        },

        data() {
            return {
                alert: false,
                lab_id: 0,
                search: '',
                start_date: {time: `${moment().format("YYYY-MM-DD")}`},
                labs_headers: [
                    {text: 'Name', value: 'nice_name', align: 'start'},
                    {text: 'Date', value: 'nice_date'},
                    {text: 'Time', value: 'nice_time'},
                    {text: 'Teachers', value: 'teacher_names'},
                    {text: 'Charons', value: 'charon_names'},
                    {text: 'Actions', value: 'actions'},
                ],
                previous_param: null,
                current_param: null,
                labDeleteText: "",
                deleteLabButtonNames: ["Yes", "No"],
            }
        },

        computed: {

            ...mapState([
                'course'
            ]),

            labs_table() {
                let startDate = moment(this.start_date.time).valueOf();
                return this.labs.reduce((r, lab) => {
                    if(+moment(lab.start.time) < startDate) return r;

                    const container = {...lab};
                    container['nice_name'] = lab.name ? lab.name : CharonFormat.getDayTimeFormat(lab.start.time);
                    container['nice_date'] = CharonFormat.getNiceDate(lab.start.time);
                    container['nice_time'] = `${CharonFormat.getNiceTime(lab.start.time)} - ${CharonFormat.getNiceTime(lab.end.time)}`;
                    container['teacher_names'] = lab.teachers.map(x => x.fullname).sort().join(', ')
                    container['charon_names'] = lab.charons.map(x => x.project_folder).sort().join(', ')

                    r.push(container);
                    return r;
                }, []);
            }
        },

        methods: {
            ...mapActions(["updateLab", "updateLabToEmpty"]),

            addNewLabSessionClicked() {
                this.updateLabToEmpty()
                window.location = "popup#/labsForm";
            },

            editLabClicked(lab) {
                this.updateLab({lab: _.cloneDeep(lab)})
                window.location = "popup#/labsForm";
            },

            promptDeletionAlert(lab) {
                this.labDeleteText = 'Lab: ' + lab.name + '\nChecking if the Lab has registrations ...';
                this.lab_id = lab.id
                Lab.checkRegistrations(this.course.id, this.lab_id, {}, (registrations) => {
                    if (registrations > 0){
                        this.labDeleteText = 'Lab: ' + lab.name + '\nLab has ' + registrations + ' registrations';
                    } else {
                        this.labDeleteText = 'Lab: ' + lab.name + '\nLab has no registrations';
                    }
                });
                this.alert = true;
            },

            deleteLab() {
                this.alert = false
                Lab.delete(this.course.id, this.lab_id, () => {
                    this.$root.$emit('refresh_labs')
                    VueEvent.$emit('show-notification', 'Lab deleted!')
                })
            },
        },

        mounted() {
            VueEvent.$on("delete-lab", (buttonName) => {
                if (buttonName === "Yes") {
                    this.deleteLab();
                } else {
                    this.alert = false;
                }
            });
        }
    }

</script>
