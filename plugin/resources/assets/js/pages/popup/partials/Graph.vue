<template>
    <apexcharts type="line" :options="options" :series="series"></apexcharts>
</template>

<script>
export default {
    name: "Graph",
    props: {
        title: {
            required: false,
            default: '',
            type: String
        },
        options: {
            required: true,
            type: Object
        },
        series: {
            required: true,
            type: Array
        }
    }
}
</script>

<style scoped>

</style>