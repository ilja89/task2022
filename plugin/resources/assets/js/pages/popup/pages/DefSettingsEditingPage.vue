<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Edit Charon settings</v-card-title>
        </v-card>

        <popup-section title="Charon Settings"
                       subtitle="Here are the general settings for each charon.">
            <v-card class="mx-auto" outlined light raised>
                <v-container class="spacing-playground pa-3" fluid>
                    <charon-settings-editing-section :charon="charon" :labs="labs"></charon-settings-editing-section>

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
    import CharonSettingsEditingSection from "../sections/CharonSettingsEditingSection";
    import Lab from "../../../api/Lab";
    import Charon from "../../../api/Charon";

    export default {
        name: "charon-settings-editing-page",
        components: {CharonSettingsEditingSection, PopupSection},
        data() {
            return {
                labs: []
            }
        },
        methods: {
            saveClicked() {
                let chosen_labs = []
                for (let i = 0; i < this.charon.charonDefenseLabs.length; i++) {
                    chosen_labs.push(this.charon.charonDefenseLabs[i].id)
                }
                let give_deadline = this.charon.defense_deadline.time
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
                Charon.saveCharonDefenseStuff(this.charon.id, give_deadline, this.charon.defense_duration,
                    chosen_labs, this.charon.choose_teacher, this.charon.defense_threshold, () => {
                        window.location = "popup#/charonSettings";
                        window.location.reload();
                        VueEvent.$emit('show-notification', 'Charon defending stuff successfully saved!')
                    })
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
            Lab.all(this.course.id, response => {
                this.labs = response
                this.getNamesForLabs()
            })
        }
    }
</script>
