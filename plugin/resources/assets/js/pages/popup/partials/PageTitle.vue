<template>
    <h1
        class="title is-3 page-title  bottom-border-separator"
        :class="{ 'title--with-right': $slots.default }"
    >
        <template v-if="hasRight">
            <div>
                {{ currentTitle }}
            </div>
            <div>
                <slot></slot>
            </div>
        </template>
        <template v-else>{{ currentTitle }}</template>
    </h1>
</template>

<script>
    export default {
        props: {
            title: {
                required: false,
                default: '',
            },
            student: {
                required: false,
                default: null,
            },
        },

        computed: {
            hasRight() {
                return !!this.$slots.default
            },

            currentTitle() {
                if (this.student !== null) {
                    return `${this.student.firstname} ${this.student.lastname}`
                } else {
                    return this.title
                }
            },
        },
    }
</script>

<style lang="scss" scoped>

    .title--with-right {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

</style>
