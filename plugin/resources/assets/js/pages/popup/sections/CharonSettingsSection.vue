<template>

    <div class="lab">

        <v-alert
                :value="alert"
                border="left"
                color="error"
        >
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
                    <v-btn @click="deleteCharon(charon_id)">Yes</v-btn>
                </v-col>
                <v-col class="shrink">
                    <v-btn @click="alert=false">No</v-btn>
                </v-col>
            </v-row>
        </v-alert>

        <v-card
                class="mx-auto"
                outlined
                hover
                light
                raised
                shaped
        >
            <v-container
                    class="spacing-playground pa-3"
                    fluid
            >
                <table class="table  is-fullwidth  is-striped  submission-counts__table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Deadline</th>
                        <th>Duration</th>
                        <th>Threshold</th>
                        <th>Labs</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="charon in charons">
                        <td>{{charon.name}}</td>
                        <td>{{getDateFormatted(charon.defense_deadline.time)}}</td>
                        <td>{{getDurationFormatted(charon.defense_duration)}}</td>
                        <td>{{charon.defense_threshold}}%</td>
                        <td>
                            <b v-for="lab in charon.charonDefenseLabs">{{lab.name}}<b
                                    v-if="lab !== charon.charonDefenseLabs[charon.charonDefenseLabs.length - 1]">, </b>
                            </b>
                        </td>
                        <td>
                            <v-btn class="ma-2" small tile outlined color="primary" @click="editClicked(charon)">Edit
                            </v-btn>
                            <v-btn class="ma-2" small tile outlined color="warning" @click="promtDeletionAlert(charon)">
                                Delete
                            </v-btn>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </v-container>
        </v-card>

    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import {Charon} from "../../../api";

    export default {
        data() {
            return {
                alert: false,
                charon_id: 0
            }
        },
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
                if (date === null) {
                    return date
                }
                return date.getDate() + '.' + ('0' + (date.getMonth() + 1)).substr(-2, 2) + '.' + date.getFullYear() +
                    ' ' + ('0' + date.getHours()).substr(-2, 2) + ':' + ('0' + date.getMinutes()).substr(-2, 2)
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
            promtDeletionAlert(charon) {
                this.alert = true
                this.charon_id = charon.charon_id
            },
            deleteCharon(charon_id) {
                Charon.deleteById(charon_id, () => {
                    this.alert = false
                    this.charon_id = 0
                    window.location.reload();
                })
            },
        },
    }
</script>
