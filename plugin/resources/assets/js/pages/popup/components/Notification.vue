<template>
    <transition name="slide-fade">
        <div v-if="show" class="is-flex popup-notification">
            <div class="notification is-success">
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
            show: { required: true }
        },

        methods: {
            onClosed() {
                VueEvent.$emit('close-notification')
            }
        }
    }
</script>
