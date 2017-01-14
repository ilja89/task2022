<template>
    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">

        <legend class="ftoggler"><slot name="title"></slot></legend>

        <slot></slot>

    </fieldset>
</template>

<script>
    export default { }
</script>
