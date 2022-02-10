<template>
    <v-tooltip
        top
        :disabled="typeof description === 'undefined' || description === null || description.length < 1"
    >
        <template v-slot:activator="{ on, attrs }">
            <span
                v-bind="attrs"
                v-on="on"
                class="label-component"
                v-bind:style="{ backgroundColor: backgroundColor }"
            >{{ name }}</span>
        </template>

        <span>{{ description }}</span>
    </v-tooltip>
</template>
<script>

export default {
    name: "LabelComponent",

    props: {
        name: { type: String, required: true, },
        description: { type: String, required: false, },
        backgroundColor: { type: String, required: true, }
    },
}

</script>
<style>

.label-component {
    color: white;
    border-radius: 2em;
    width: auto;
    height: auto;
    padding: 0.5em;
}

</style>
