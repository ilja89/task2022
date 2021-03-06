<template>
    <div v-if="render" class="load-background">
        <div class="load-container">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        render: {
            render: Boolean,
            default: false
        },
    },
}
</script>

<style lang="scss" scoped>

.load-background {
    position: absolute;
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    backdrop-filter: blur(4px);
}

.load-container {
    display: inline-block;
    position: absolute;
    width: 80px;
    height: 80px;
    top: 15%;
    left: 50%;
    transform: translate(-50%, -15%);

    div {
        position: absolute;
        top: 33px;
        width: 13px;
        height: 13px;
        border-radius: 50%;
        background: #59c2e6;
        animation-timing-function: cubic-bezier(0, 1, 1, 0);

        &:nth-child(1) {
            left: 8px;
            animation: anim1 0.6s infinite;
        }

        &:nth-child(2) {
            left: 8px;
            animation: anim2 0.6s infinite;
        }

        &:nth-child(3) {
            left: 32px;
            animation: anim2 0.6s infinite;
        }

        &:nth-child(4) {
            left: 56px;
            animation: anim3 0.6s infinite;
        }
    }
}

@keyframes anim1 {
    0% {
        transform: scale(0);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes anim3 {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(0);
    }
}

@keyframes anim2 {
    0% {
        transform: translate(0, 0);
    }
    100% {
        transform: translate(24px, 0);
    }
}

</style>
