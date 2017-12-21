<template>
    <div class="select" :class="[ sizeClass ]">
        <select
            :name="name"
            v-model="newValue"
        >
            <option
                v-for="option in options"
                :value="option[valueKey]"
            >
                {{ option[placeholderKey] }}
            </option>
        </select>
    </div>
</template>

<script>
    export default {
        name: "popup-select",

        props: {
            name: {
                required: false,
                type: String,
                default: null,
            },
            options: {
                required: true,
                type: Array,
            },
            value: {
                required: false,
                default: null,
                type: [String, Number, Boolean],
            },
            valueKey: {
                required: false,
                default: 'value',
                type: [String, Number],
            },
            placeholderKey: {
                required: false,
                default: 'placeholder',
                type: [String, Number, Boolean],
            },
            size: {
                required: false,
                default: null,
                type: String,
            },
        },

        data() {
            return {
                newValue: null,
            }
        },

        computed: {
            sizeClass() {
                return this.size ? 'is-' + this.size : ''
            },
        },

        watch: {
            newValue() {
                if (typeof this.newValue !== 'undefined') {
                    this.$emit('input', this.newValue)
                }
            },

            value() {
                if (this.value !== this.newValue) {
                    this.newValue = this.value
                }
            },

            options() {
                this.resetOptions()
            },
        },

        methods: {
            resetOptions() {
                if (this.options.length) {
                    this.newValue = this.options[0][this.valueKey]
                }
            },
        },

        mounted() {
            if (this.value) {
                this.newValue = this.value
            } else {
                this.resetOptions()
            }
        },
    }
</script>
