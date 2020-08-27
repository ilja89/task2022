<template>
    <div class="modal" :class="{ 'is-active': isActive }">
        <div class="modal-background" @click="$emit('modal-was-closed')"></div>
        <div class="modal-card" v-bind:style="{width: computedWidth}">
            <header class="modal-card-head">
                <slot name="header"></slot>
                <button class="delete" @click="$emit('modal-was-closed')"></button>
            </header>
            <section class="modal-card-body">
                <slot></slot>
            </section>
            <footer class="modal-card-foot">
                <slot name="footer">
                    <a class="button" @click="$emit('modal-was-closed')">Close</a>
                </slot>
            </footer>
        </div>
    </div>
</template>

<script>
    export default {

        props: {
            isActive: { required: true },
            wide: { required: false }
        },
        computed: {
            computedWidth: function() {
                if (this.wide) {
                    return '95%';
                }
                return ''
            }
        }
    }
</script>
<style scoped>
    .wide_modal {
        width: 95%;
    }
</style>
