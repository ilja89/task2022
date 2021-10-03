<template>
    <popup-section title="Submissions graphs" subtitle="Graphs showing the number of submissions data">

        <template slot="header-right">
          <v-btn class="ma-2" tile outlined color="primary" @click="fetchSubmissions">Load Data</v-btn>
        </template>

        <v-card-title v-if="value">
          <graph :title="graphTitleEveryDay" :options="optionsEveryDay" :series="seriesEveryDay"></graph>
          <graph :title="graphTitleToday" :options="optionsToday" :series="seriesToday"></graph>
        </v-card-title>
        <v-card-title v-else>
            {{ empty }}
        </v-card-title>

    </popup-section>
</template>

<script>
    import {PopupSection} from '../layouts/index'
    import VueApexCharts from "vue-apexcharts";
    import {Graph} from "../partials";

    export default {

        name: 'submission-graph-section',
        components: {PopupSection, apexcharts: VueApexCharts, Graph},

        data() {
            return {
              value: false,
              empty: 'Click on Load Data to show the data',
              graphTitleEveryDay: 'Graph showing the number of submissions for every day',
              graphTitleToday: 'Graph showing the number of submissions for today',
            }
        },

        props: {
          graphDataEveryDay: {
            required: true,
            default: []
          },
          graphDataToday: {
            required: true,
            default: []
          }
        },

        computed: {
          optionsEveryDay() {
            return {
              xaxis: {
                categories: this.graphDataEveryDay.map(sub => sub.dateRow)
              }
            }
          },

          seriesEveryDay() {
            return [{
                name: 'submissions',
                data: this.graphDataEveryDay.map(sub => sub.count)
              }]
          },

          optionsToday() {
            return {
              xaxis: {
                categories: this.graphDataToday.map(sub => sub.time)
              }
            }
          },

          seriesToday() {
            return [{
              name: 'submissions',
              data: this.graphDataToday.map(sub => sub.count)
            }]
          }
        },

        methods: {
          fetchSubmissions() {
              this.value = true;
            },
        },
    }
</script>

<style scoped>

</style>