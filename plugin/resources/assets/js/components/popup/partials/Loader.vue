<template>
    <div id="loader" v-if="is_visible">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            visible: { required: true }
        },

        data() {
            return {
                is_visible: this.visible
            };
        },

        mounted() {
            VueEvent.$on('show-loader', () => this.is_visible = true);
            VueEvent.$on('hide-loader', () => this.is_visible = false);
        }
    }
</script>
