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

<style lang="scss" scoped>

    @import '../../../../sass/popup/variables';

    .submission-info:not(:last-of-type) {
        margin-bottom: .75rem;
    }

    .submission-info__title {
        color: $grey;
        font-weight: 500;
        font-size: 1.25rem;
    }

    .submission-info__content {
        font-weight: 500;
        font-size: 1.25rem;
    }

</style>
