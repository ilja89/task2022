<template>
    <div class="loading__overlay" v-if="visible">
        <div class="loading__spinner">
            <div class="loading__bounce  loading__bounce--1"></div>
            <div class="loading__bounce  loading__bounce--2"></div>
            <div class="loading__bounce  loading__bounce--3"></div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            visible: {
                type: Boolean,
                required: true,
            },
        },
    }
</script>

<style lang="scss" scoped>

    .loading__overlay {
        position: fixed;
        width:  100%;
        height: 100%;
        top:  0;
        left: 0;

        background-color: rgba(255, 255, 255, 0.2);
        z-index: 999;
    }

    .loading__spinner {
        position: fixed;
        width: 100%;
        top: 50%;

        text-align: center;
    }

    .loading__bounce {
        display: inline-block;
        width:  18px;
        height: 18px;

        background-color: #59c2e6;
        border-radius: 100%;

        -webkit-animation: sk-bouncedelay 1.4s infinite ease-in-out both;
        animation: sk-bouncedelay 1.4s infinite ease-in-out both;
    }

    .loading__bounce--1 {
        -webkit-animation-delay: -0.32s;
        animation-delay: -0.32s;
    }

    .loading__bounce--2 {
        -webkit-animation-delay: -0.16s;
        animation-delay: -0.16s;
    }

    @-webkit-keyframes sk-bouncedelay {
        0%, 80%, 100% { -webkit-transform: scale(0) }
        40% { -webkit-transform: scale(1.0) }
    }

    @keyframes sk-bouncedelay {
        0%, 80%, 100% {
            -webkit-transform: scale(0);
            transform: scale(0);
        } 40% {
              -webkit-transform: scale(1.0);
              transform: scale(1.0);
          }
    }
</style>
