<template>
    <v-app>
        <v-content>
            <div class="popup-container">
                <popup-header/>

                <popup-body/>

                <loader :visible="loaderVisible !== 0"/>

                <notification
                        :text="notification.text"
                        :show="notification.show"
                        :type="notification.type"
                />
            </div>
        </v-content>
    </v-app>
</template>

<script>
    import {PopupHeader, PopupBody} from './layouts'
    import {Loader} from './partials'
    import {Notification} from '../../components/partials'

    export default {

        components: {PopupHeader, PopupBody, Loader, Notification},

        data() {
            return {
                loaderVisible: 0,
                notificationText: '',
                notificationShow: false,
                notification: {
                    text: '',
                    show: false,
                    type: 'success',
                }
            }
        },

        mounted() {
            this.initializeEventListeners()
        },

        methods: {
            showNotification(message, type, timeout) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type
                setTimeout(() => {
                    this.notification.show = false
                }, timeout)
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
