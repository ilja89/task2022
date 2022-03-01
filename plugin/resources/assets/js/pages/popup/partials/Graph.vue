<template>
  <div class="chart-container">
    <div class="text-box">
      <div class="graph-text">{{ title }}</div>
    </div>
    <apexcharts class="chart" height="380" type="line" :options="options" :series="series"></apexcharts>
  </div>
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
.chart-container {
  display: block;
  width: 100%;
}
.chart {
  display:block;
  width:100%;
}
.text-box {
  min-height:60px
}
.graph-text {
  position:relative;
  top:30px
}
</style>