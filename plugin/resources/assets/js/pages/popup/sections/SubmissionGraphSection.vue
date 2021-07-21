<template>
    <popup-section title="Submissions graph"
                   subtitle="Graph showing the number of submissions for every day">

        <template slot="header-right">
          <v-btn class="ma-2" tile outlined color="primary" @click="fetchSubmissions">Load Data</v-btn>
        </template>

        <v-card-title v-if="value">
          <template>
              <apexcharts class="chart" height="380" type="line" :options="chartOptions" :series="series"></apexcharts>
          </template>
        </v-card-title>
        <v-card-title v-else>
            {{ empty }}
        </v-card-title>

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import VueApexCharts from "vue-apexcharts";

    export default {

        name: 'submission-graph-section',
        components: {PopupSection, apexcharts: VueApexCharts},

        data() {
            return {
                value: false,
                empty: 'Click on Load Data to show the data',
              chartOptions: {
                xaxis: {
                  categories: ["13/07/2021", "14/07/2021", "15/07/2021", "16/07/2021", "17/07/2021", "18/07/2021", "19/07/2021", "20/07/2021"]
                }
              },
              series: [{
                name: 'submissions',
                data: [8, 21, 45, 50, 55, 60, 70, 91]
              }]
            }
        },

        computed: {
        },

        methods: {
            fetchSubmissions() {
              this.value = true;
            },
        },
    }
</script>

<style scoped>
  .chart {
    display:block;
    width: 100%;
  }
</style>