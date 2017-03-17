<template>
    <div class="popup-container">

        <popup-header :course_id="context.course_id" :student="context.active_student" :charon="context.active_charon">
        </popup-header>

        <popup-body :context="context"></popup-body>

        <loader :visible="loaderVisible !== 0"></loader>
        <notification :text="notificationText" :show="notificationShow"></notification>
    </div>
</template>

<script>
    import { PopupHeader } from './layouts';
    import { PopupBody } from './layouts/PopupBody.vue';
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
            };
        },

        mounted() {
            this.initializeEventListeners();
        },

        methods: {
            showNotification(message) {
                this.notificationText = message;
                this.notificationShow = true;
                setTimeout(() => {
                    this.notificationShow = false;
                }, 2000);
            },

            hideLoader() {
                if (this.loaderVisible !== 0) {
                    this.loaderVisible--;
                }
            },

            initializeEventListeners() {
                VueEvent.$on('show-notification', message => this.showNotification(message));
                VueEvent.$on('close-notification', () => this.showNotification = false);
                VueEvent.$on('show-loader', () => this.loaderVisible += 1);
                VueEvent.$on('hide-loader', () => this.hideLoader());
            }
        }
    }
</script>
