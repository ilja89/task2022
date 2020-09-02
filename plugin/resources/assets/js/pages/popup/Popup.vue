<template>
    <v-app>
        <v-main>
            <div class="popup-container">
                <popup-header/>

                <popup-body/>

                <loader :visible="loaderVisible !== 0"/>

                <v-snackbar
                        top
                        right
                        multi-line
                        absolute
                        shaped
                        v-model="notification.show"
                        :timeout="notification.timeout"
                >
                    {{ notification.text }}

                    <template v-slot:action="{ attrs }">
                        <v-btn
                                color="blue"
                                text
                                v-bind="attrs"
                                @click="notification.show = false"
                        >
                            Close
                        </v-btn>
                    </template>
                </v-snackbar>

            </div>
        </v-main>
    </v-app>
</template>

<script>
    import {PopupHeader, PopupBody} from './layouts'
    import {Loader} from './partials'
    import {Notification} from '../../components/partials'

    export default {

        components: {PopupHeader, PopupBody, Loader},

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

        mounted() {
            this.initializeEventListeners()
        },

        methods: {
            showNotification(message, type, timeout = 2000) {
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
                VueEvent.$on('show-notification', (message, type = 'success', timeout = 2000) => {
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
    @import url("https://fonts.googleapis.com/css?family=Material+Icons");
</style>