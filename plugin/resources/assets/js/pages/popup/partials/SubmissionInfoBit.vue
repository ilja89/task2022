<template>
    <div class="submission-info">
        <div class="submission-info__title">{{ title }}:</div>
        <div class="submission-info__content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {

        name: 'submission-info-bit',

        props: {
            title: {
                required: true,
                type: String,
            },
        },
    }
</script>

