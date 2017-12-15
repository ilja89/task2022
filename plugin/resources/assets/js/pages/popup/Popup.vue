<template>
    <div class="popup-container">

        <popup-header
                :student="student"
                :charon="charon"
                :submission="submission"
        >
        </popup-header>

        <popup-body></popup-body>

        <loader :visible="loaderVisible !== 0"></loader>
        <notification :text="notification.text" :show="notification.show" :type="notification.type"></notification>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import { PopupHeader, PopupBody } from './layouts'
    import { Loader } from './components'
    import { Notification } from '../../components/partials'

    export default {

        components: { PopupHeader, PopupBody, Loader, Notification },

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

        computed: {
            ...mapState([
                'student',
                'charon',
                'submission',
            ]),
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
                    this.loaderVisible--;
                }
            },

            initializeEventListeners() {
                VueEvent.$on('show-notification', (message, type = 'success', timeout = 2000) => {
                    this.showNotification(message, type, timeout)
                });
                VueEvent.$on('close-notification', () => this.notification.show = false);
                VueEvent.$on('show-loader', () => this.loaderVisible += 1);
                VueEvent.$on('hide-loader', () => this.hideLoader());
            }
        }
    }
</script>
