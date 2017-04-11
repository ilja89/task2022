<template>
    <transition name="slide-fade">
        <div v-if="show" class="is-flex popup-notification">
            <div class="notification" :class="[ notificationClass ]">
                <button class="delete" @click="onClosed()"></button>
                {{ text }}
            </div>
        </div>
    </transition>
</template>

<script>
    export default {
        props: {
            text: { required: true },
            show: { required: true },
            type: { required: false, default: 'success' },
        },

        computed: {
            notificationClass() {
                return 'is-' + this.type
            }
        },

        methods: {
            onClosed() {
                VueEvent.$emit('close-notification')
            }
        }
    }
</script>
