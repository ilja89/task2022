<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler" v-on:click="open = !open">
            Deadlines
        </legend>



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
