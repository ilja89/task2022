<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler"><slot name="title"></slot>
            <span class="section-title-right">
                <span class="section-title-right__advanced-text">Advanced</span>
                <span class="toggle-container">
                    <input :id="toggle_id" class="toggle toggle-light" type="checkbox" @click="onAdvancedToggle"
                           :checked="advanced_checked"
                    >
                    <label class="toggle-btn" :for="toggle_id"></label>
                </span>
            </span>
        </legend>

        <slot></slot>

    </fieldset>
</template>

<script>
    export default {
        props: [ 'toggle_id' ],

        data() {
            return {
                advanced_checked: 0
            };
        },

        methods: {
            onAdvancedToggle() {
                // not this.advanced_checked because the event is fired before it can change. So we invert it.
                this.advanced_checked = !this.advanced_checked;
                this.$emit('advanced-was-toggled', this.advanced_checked);
            }
        }
    }
</script>
