<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr_CF">

        <legend class="ftoggler" v-on:click="sectionOpen = !sectionOpen">
            <slot name="title"></slot>
        </legend>

        <slot v-if="sectionOpen"></slot>

    </fieldset>
</template>

<script>
    export default {

        props: {
            open: {
                required: false,
                default: true
            }
        },

        data() {
            return {
                sectionOpen: this.open
            };
        }
    }
</script>

<style>
    .ftoggler {
        cursor: pointer;
    }
</style>
