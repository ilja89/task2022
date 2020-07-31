<template>
    <div>
        <page-title :title="'Defense settings'"></page-title>
        <defense-settings-section :charons="charons"></defense-settings-section>
    </div>
</template>

<script>
    import PageTitle from "../partials/PageTitle";
    import DefenseSettingsSection from "../sections/DefenseSettingsSection";
    import Charon from "../../../api/Charon";
    import {mapState} from "vuex";
    import Lab from "../../../api/Lab";

    export default {
        name: "defense-settings-page",
        components: { PageTitle, DefenseSettingsSection },
        data() {
            return {
                charons: [],
                charonCountdown: null
            }
        },
        computed: {

            ...mapState([
                'course'
            ]),
        },
        methods: {
            formatCharonsDeadlines(ch, then) {
                this.charonCountdown = ch.length
                for (let i = 0; i < ch.length; i++){
                    this.getLabsForCharon(ch[i].id, result => {
                        ch[i].defense_labs = result
                        then(ch)
                    })
                    if (ch[i].defense_deadline === null) {
                        ch[i].defense_deadline = {time: null}
                    } else {
                        let deadline = new Date(ch[i].defense_deadline)
                        ch[i].defense_deadline = {time: deadline}
                    }
                }
            },
            getLabsForCharon(charonId, then) {
                Lab.getByCharonId(charonId, response => {
                    this.getNamesForLabs(response, result => {
                        then(response)
                    })

                })
            },
            getNamesForLabs(labs, then) {
                for (let i = 0; i < labs.length; i++) {
                    labs[i].name = this.getDayTimeFormat(new Date(labs[i].start))
                        + ' (' + this.getNiceDate(new Date(labs[i].start)) + ')'
                }
                then(labs)
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
        },
        mounted() {
            Charon.all(this.course.id, response => {
                this.formatCharonsDeadlines(response, done => {
                    this.charonCountdown--
                    if (!this.charonCountdown) {
                        this.charons = done
                    }
                })
            })
        }
    }
</script>
