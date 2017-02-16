<template>
    <div id="loader" v-if="visible">
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
        }
    }
</script>
