<template>
    <popup-section title="Submissions graph"
                   subtitle="Graph showing the number of submissions for every day">

        <template slot="header-right">
          <v-btn class="ma-2" tile outlined color="primary" @click="fetchSubmissionCounts">Load Data</v-btn>
        </template>

        <v-card-title v-if="value">
          <template>
            <div class="example">
              <apexcharts width="500" height="350" type="line" :options="chartOptions" :series="series"></apexcharts>
            </div>
          </template>
        </v-card-title>
        <v-card-title v-else>
            {{ empty }}
        </v-card-title>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {PopupSection} from '../layouts/index'
    import VueApexCharts from "vue-apexcharts";

    export default {

        name: 'submission-graph-section',
        components: {PopupSection, apexcharts: VueApexCharts},

        data() {
            return {
                value: false,
                search: '',
                empty: 'Click on Load Data to show the data',
              chartOptions: {
                chart: {
                  id: 'basic-bar'
                },
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
            ...mapGetters([
                'courseId',
            ]),
        },

        methods: {
            fetchSubmissionCounts() {
              this.value = true;
            },
        },
    }
</script>
