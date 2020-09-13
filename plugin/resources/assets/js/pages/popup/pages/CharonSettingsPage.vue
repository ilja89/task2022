<template>
    <div>
        <v-card class="mb-16 pl-4">
            <v-card-title>Charon settings</v-card-title>
        </v-card>

        <charon-settings-section :charons="charons"></charon-settings-section>

        <tester-type-section :testerTypes="testerTypes" :course-id="this.course.id"></tester-type-section>
    </div>
</template>

<script>
    import CharonSettingsSection from "../sections/CharonSettingsSection";
    import TesterTypeSection from "../sections/TesterTypeSection";
    import Charon from "../../../api/Charon";
    import Course from "../../../api/Course";
    import {mapState} from "vuex";

    export default {
        name: "defense-settings-page",
        components: {CharonSettingsSection, TesterTypeSection},
        data() {
            return {
                charons: [],
                testerTypes: []
            }
        },
        computed: {

            ...mapState([
                'course'
            ]),
        },
        methods: {
            formatCharonsDeadlines(ch, then) {
                for (let i = 0; i < ch.length; i++) {
                    this.getNamesForLabs(ch[i].charonDefenseLabs)
                    if (ch[i].defense_deadline === null) {
                        ch[i].defense_deadline = {time: null}
                    } else {
                        let deadline = new Date(ch[i].defense_deadline)
                        ch[i].defense_deadline = {time: deadline}
                    }
                }
                then(ch)
            },
            getNamesForLabs(labs) {
                for (let i = 0; i < labs.length; i++) {
                    labs[i].name = this.getDayTimeFormat(new Date(labs[i].start))
                        + ' (' + this.getNiceDate(new Date(labs[i].start)) + ')'
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
        },
        mounted() {
            Charon.all(this.course.id, response => {
                this.formatCharonsDeadlines(response, done => {
                    this.charons = done
                })
            })

            Course.getTesterTypes(this.course.id, response => {
                this.testerTypes = response
            })
        }
    }
</script>
