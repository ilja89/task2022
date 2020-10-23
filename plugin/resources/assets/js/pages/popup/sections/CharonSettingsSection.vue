<template>

    <popup-section title="Charon Settings"
                   subtitle="Here are the general settings for each charon.">
        <v-alert :value="alert" border="left" color="error" outlined>
            <v-row align="center" justify="space-between">
                <v-col class="grow">
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    Are you sure you want to delete the charon?
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                    <md-icon>warning</md-icon>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="deleteCharon">Yes</v-btn>
                </v-col>
                <v-col class="shrink">
                    <v-btn class="ma-2" small tile outlined color="error" @click="alert=false">No</v-btn>
                </v-col>
            </v-row>
        </v-alert>

        <v-card-title v-if="charons.length">
            Charons
            <v-spacer></v-spacer>
            <v-text-field
                    v-if="charons.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
            </v-text-field>
        </v-card-title>
        <v-card-title v-else>
            No Charons for this course!
        </v-card-title>

        <v-data-table
                v-if="charons.length"
                :headers="charons_headers"
                :items="charons_table"
                :search="search">
            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn class="ma-2" small tile outlined color="primary" @click="editClicked(item)">Edit
                </v-btn>
                <v-btn class="ma-2" small tile outlined color="error" @click="promptDeletionAlert(item)">
                    Delete
                </v-btn>
            </template>
        </v-data-table>

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import {mapActions} from "vuex";
    import {Charon} from "../../../api";

    export default {
        data() {
            return {
                alert: false,
                charon_id: 0,
                search: '',
                charons_headers: [
                    {text: 'Charon', value: 'name', align: 'start'},
                    {text: 'Start time', value: 'formatted_start_time'},
                    {text: 'Deadline', value: 'formatted_deadline'},
                    {text: 'Duration', value: 'formatted_duration'},
                    {text: 'Threshold', value: 'nice_defense_threshold'},
                    {text: 'Labs', value: 'labs_string'},
                    {text: 'Actions', value: 'actions'}
                ],
            }
        },

        computed: {
            charons_table() {
                return this.charons.map(charon => {
                    const container = {...charon};

                    container['formatted_deadline'] = this.getDateFormatted(charon.defense_deadline.time);
                    container['formatted_start_time'] = this.getDateFormatted(charon.defense_start_time.time);
                    container['formatted_duration'] = this.getDurationFormatted(charon.defense_duration);
                    container['labs_string'] = this.getLabsStringForCharon(charon.charonDefenseLabs);
                    container['nice_defense_threshold'] = `${charon.defense_threshold}%`

                    return container;
                });
            }
        },

        components: {PopupSection},

        props: {
            charons: {required: true}
        },

        methods: {
            ...mapActions(["updateCharon"]),

            editClicked(charon) {
                this.updateCharon({charon});
                window.location = 'popup#/defSettingsEditing'
            },

            getDurationFormatted(duration) {
                if (duration !== null) {
                    return duration + ' min'
                }
            },

            getDateFormatted(date) {
                try {
                    return date.getDate() + '.' + ('0' + (date.getMonth() + 1)).substr(-2, 2) + '.' + date.getFullYear() +
                        ' ' + ('0' + date.getHours()).substr(-2, 2) + ':' + ('0' + date.getMinutes()).substr(-2, 2)
                } catch (e) {
                    return date;
                }
            },

            getDayTimeFormat(start) {
                let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                return daysDict[start.getDay()] + start.getHours();
            },

            getNiceDate(date) {
                let month = (date.getMonth() + 1).toString();
                if (month.length == 1) {
                    month = "0" + month
                }
                return date.getDate() + '.' + month + '.' + date.getFullYear()
            },

            getNameForLab(labStart) {
                return this.getDayTimeFormat(new Date(labStart))
                    + ' (' + this.getDateFormatted(new Date(labStart)) + ')'
            },

            getThreshold(percentage) {
                if (percentage === null) {
                    return '-'
                }
                return percentage + '%'
            },

            getLabsStringForCharon(labs) {
                return labs.map(x => x.name).join(', ')
            },

            promptDeletionAlert(charon) {
                this.alert = true
                this.charon_id = charon.id
            },

            deleteCharon() {
                Charon.deleteById(this.charon_id, () => {
                    this.alert = false
                    this.charon_id = 0
                    window.location.reload();
                })
            },
        },
    }
</script>
