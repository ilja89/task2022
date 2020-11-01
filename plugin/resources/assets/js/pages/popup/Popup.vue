<template>
    <v-app style="background: lightgray">
        <v-main>

            <popup-header/>

            <popup-navigation/>

            <popup-body/>

            <loader :visible="loaderVisible !== 0"/>

            <v-snackbar top right multi-line shaped v-model="notification.show"
                        :timeout="notification.timeout">
                {{ notification.text }}

                <template v-slot:action="{ attrs }">
                    <v-btn color="blue" text v-bind="attrs" @click="notification.show = false">
                        Close
                    </v-btn>
                </template>
            </v-snackbar>

            <v-footer absolute class="font-weight-medium">
                <v-col class="text-center" cols="12">
                    {{ new Date().getFullYear() }} — <strong>Charon</strong>
                </v-col>
            </v-footer>
        </v-main>
    </v-app>
</template>

<script>
    import {PopupHeader, PopupBody, PopupNavigation} from './layouts'
    import {Loader} from './partials'
    import Charon from "../../api/Charon";
    import {mapState} from "vuex";

    export default {

        components: {PopupHeader, PopupBody, Loader, PopupNavigation},

        data() {
            return {
                loaderVisible: 0,
                notification: {
                    text: '',
                    show: false,
                    type: 'success',
                    timeout: 1000,
                }
            }
        },

        computed: {
            ...mapState([
                'course',
                'charons'
            ]),
        },

        created() {
            this.initializeEventListeners()

            Charon.all(this.course.id, response => {
                this.formatCharonDateTimes(response, done => {
                    this.charons = done
                })
            })
        },

        methods: {
            formatCharonDateTimes(ch, then) {
                for (let i = 0; i < ch.length; i++) {
                    this.getNamesForLabs(ch[i].charonDefenseLabs)
                    if (ch[i].defense_deadline === null) {
                        ch[i].defense_deadline = {time: null}
                    } else {
                        let deadline = new Date(ch[i].defense_deadline)
                        ch[i].defense_deadline = {time: deadline}
                    }

                    if (ch[i].defense_start_time === null) {
                        ch[i].defense_start_time = {time: null}
                    } else {
                        let startTime = new Date(ch[i].defense_start_time)
                        ch[i].defense_start_time = {time: startTime}
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
                try {
                    let daysDict = {0: 'P', 1: 'E', 2: 'T', 3: 'K', 4: 'N', 5: 'R', 6: 'L'};
                    return daysDict[start.getDay()] + start.getHours();
                } catch (e) {
                    return ""
                }
            },
            getNiceDate(date) {
                try {
                    let month = (date.getMonth() + 1).toString();
                    if (month.length === 1) {
                        month = "0" + month
                    }
                    return date.getDate() + '.' + month + '.' + date.getFullYear()
                } catch (e) {
                    return ""
                }
            },

            showNotification(message, type, timeout = 5000) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type
                this.notification.timeout = timeout
            },

            hideLoader() {
                if (this.loaderVisible !== 0) {
                    this.loaderVisible--
                }
            },

            initializeEventListeners() {
                VueEvent.$on('show-notification', (message, type = 'success', timeout = 5000) => {
                    this.showNotification(message, type, timeout)
                });
                VueEvent.$on('close-notification', _ => this.notification.show = false)
                VueEvent.$on('show-loader', _ => this.loaderVisible += 1)
                VueEvent.$on('hide-loader', _ => this.hideLoader())
            },
        },
    }
</script>

<style>
    @import url("https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css");
    @import url("https://fonts.googleapis.com/css?family=Material+Icons");
</style>