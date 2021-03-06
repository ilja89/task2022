<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler" v-on:click="open = !open">
            <slot name="title"></slot>
        </legend>

        <slot v-if="open"></slot>

    </fieldset>
</template>

<script>
    export default {
        data() {
            return {
                open: true
            };
        }
    }
</script>

<style>
    .ftoggler {
        cursor: pointer;
    }
</style>
