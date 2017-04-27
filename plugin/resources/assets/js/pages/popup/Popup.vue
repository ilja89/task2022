<template>
    <div class="popup-container">

        <popup-header :course_id="context.course_id" :student="context.active_student" :charon="context.active_charon">
        </popup-header>

        <popup-body :context="context"></popup-body>

        <loader :visible="loaderVisible !== 0"></loader>
        <notification :text="notification.text" :show="notification.show" :type="notification.type"></notification>
    </div>
</template>

<script>
    import { PopupHeader, PopupBody } from './layouts';
    import { Loader, Notification } from './components';

    export default {

        components: { PopupHeader, PopupBody, Loader, Notification },

        props: {
            context: { required: true }
        },

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
            };
        },

        mounted() {
            this.initializeEventListeners();
        },

        methods: {
            showNotification(message, type) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type
                setTimeout(() => {
                    this.notification.show = false
                }, 2000)
            },

            hideLoader() {
                if (this.loaderVisible !== 0) {
                    this.loaderVisible--;
                }
            },

            initializeEventListeners() {
                VueEvent.$on('show-notification', (message, type) => {
                    if (typeof type === 'undefined') {
                        this.showNotification(message, 'success')
                    } else {
                        this.showNotification(message, type)
                    }
                });
                VueEvent.$on('close-notification', () => this.notification.show = false);
                VueEvent.$on('show-loader', () => this.loaderVisible += 1);
                VueEvent.$on('hide-loader', () => this.hideLoader());
            }
        }
    }
</script>
