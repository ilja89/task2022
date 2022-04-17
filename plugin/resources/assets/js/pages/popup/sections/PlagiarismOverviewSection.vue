<template>
    <popup-section
        title="Plagiarism overview">

        <template slot="header-right">
            <v-btn
                class="ma-2"
                tile
                outlined
                color="primary"
                :disabled="!matchesExist || graphsLoaded"
                @click="loadGraphs"
            >
                Load graphs
            </v-btn>
        </template>


        <div v-if="graphsLoaded">
            <v-alert :value="filtrationOn" border="left" color="error" outlined>
                Matches filtration is on. To see all matches, clear filtration
            </v-alert>

            Acceptable matches:
            <toggle-button :buttonDefault="true" @buttonClicked="showAcceptableNodesSwitch($event)"></toggle-button>
            Plagiarism matches:
            <toggle-button :buttonDefault="true" @buttonClicked="showPlagiarismNodesSwitch($event)"></toggle-button>
            New matches:
            <toggle-button :buttonDefault="true" @buttonClicked="showNewNodesSwitch($event)"></toggle-button>
            <div v-if="showAcceptableNodes || showPlagiarismNodes || showNewNodes">
                <v-row justify="space-around" class="mt-5">
                    <v-col cols="12" md="6">
                        <apexcharts
                            type="bar"
                            class="graph apexGraph"
                            :options="charts.barChart.chartOptions"
                            :series="charts.barChart.series">
                        </apexcharts>
                    </v-col>

                    <v-col cols="12" md="4">
                        <apexcharts
                            type="donut"
                            class="graph apexGraph"
                            :options="donutOptions"
                            :series="donutSeries">
                        </apexcharts>
                    </v-col>
                </v-row>

                <v-row justify="center" class="mt-12">
                    <v-col cols="12" md="6">
                        <VisNetwork
                            class="graph"
                            :nodes="networkNodes"
                            :edges="networkEdges"
                            :matches="matches">
                        </VisNetwork>
                    </v-col>
                </v-row>
            </div>

        </div>

        <div v-else>
            {{ empty }}
        </div>

    </popup-section>
</template>

<script>
import PopupSection from "../layouts/PopupSection";
import VueApexCharts from "vue-apexcharts";
import VisNetwork from "./VisNetwork";
import {NEUTRAL, INTERESTING, SUSPICIOUS, WARNING, DANGER, valueToGroup} from '../../../helpers/PlagiarismColors';
import ToggleButton from "../../../components/partials/ToggleButton";

export default {
    name: "PlagiarismOverviewSection",
    components: {PopupSection, 'apexcharts': VueApexCharts, VisNetwork, ToggleButton},
    props: ['matches', 'filtrationOn'],

    created() {
        VueEvent.$on('refresh-plagiarism-overview', () => {
            this.parseMatches(this.matches)
        })
    },

    data() {
          return {
              charts: {
                  barChart: {
                      chartOptions: {
                          title: {
                              text: 'Distribution'
                          },
                          subtitle: {
                              text: 'Bar chart shows percentage based distribution'
                          },
                          chart: {
                              type: 'bar',
                              width: 650,
                              height: 450,
                              background: '#f0ffff'
                          },
                          xaxis: {
                              categories: ['0-19', '20-39', '40-59', '60-79', '80-100'],
                          },
                      },
                      series: [{
                          name: 'matches',
                          data: [0, 0, 0, 0, 0]
                      }]
                  },

                  donutChart: {
                      chartOptions: {
                          title: {
                              text: 'Matches'
                          },
                          subtitle: {
                              text: 'Found 3 matches.'
                          },
                          chart: {
                              type: 'donut',
                              width: 400,
                              height: 400,
                          },
                          labels: ['Acceptable', 'New', 'Plagiarism'],
                      },
                      series: [0, 0, 0]
                  },

                  networkChart: {
                      chartNodes: [],
                      chartEdges: []
                  },
              },
              empty: 'No graphs loaded',
              loadGraphsTooltip: 'Fetch matches first',
              graphsLoaded: false,
              showAcceptableNodes: true,
              showPlagiarismNodes: true,
              showNewNodes: true,
          }
      },

      watch: {
          matches: function (newMatches, oldMatches) {
              if (this.matches) {
                  this.parseMatches(newMatches)
              } else {
                  this.charts.networkChart.chartNodes = []
                  this.charts.networkChart.chartEdges = []
              }
          }
      },

      computed: {
          barOptions() {
              return this.charts.barChart.chartOptions
          },

          barSeries() {
              return this.charts.barChart.series
          },

          donutOptions() {
              return this.charts.donutChart.chartOptions
          },

          donutSeries() {
              return this.charts.donutChart.series
          },

          networkNodes() {
              return this.charts.networkChart.chartNodes
          },

          networkEdges() {
              return this.charts.networkChart.chartEdges
          },

          matchesExist() {
              return this.matches && this.matches.length
          }
      },

      methods: {
          parseMatches(newMatches) {
              this.parseBarDonutCharts(newMatches)
              this.parseNetworkChart(newMatches)
          },

          parseBarDonutCharts(newMatches) {
              let categories = {
                  '0-19': 0,
                  '20-39': 0,
                  '40-59': 0,
                  '60-79': 0,
                  '80-100': 0
              }
              let labels = {
                  'acceptable': 0,
                  'new': 0,
                  'plagiarism': 0
              }
              newMatches.forEach(match => {
                  let status = match.status
                  if ((status === 'new' && this.showNewNodes) ||
                      (status === 'acceptable' && this.showAcceptableNodes) ||
                      (status === 'plagiarism' && this.showPlagiarismNodes)) {
                      let averagePercentage = (match.percentage + match.other_percentage) / 2
                      for (let category in categories) {
                        let range = category.split('-')
                        let lower = parseInt(range[0])
                        let upper = parseInt(range[1])
                        if (averagePercentage > lower && averagePercentage < upper) {
                          categories[category] += 1
                        }
                      }
                      labels[status] = labels[status] + 1
                  }
              })

              this.charts.barChart.series = [{
                  name: 'matches',
                  data: Object.values(categories)
              }]
              this.charts.donutChart.series = Object.values(labels)
          },

          parseNetworkChart(newMatches) {
              const nodes = []
              const groups = {[NEUTRAL]: 0, [INTERESTING]: 0, [SUSPICIOUS]: 0, [WARNING]: 0, [DANGER]: 0}
              let edges = []

              const nodesById = {}

              for (let i = 0; i < newMatches.length; i++) {
                  const match = newMatches[i]
                  let status = match.status
                  if ((status === 'new' && this.showNewNodes) ||
                      (status === 'acceptable' && this.showAcceptableNodes) ||
                      (status === 'plagiarism' && this.showPlagiarismNodes)) {
                      const one = match.uniid
                      const other = match.other_uniid

                      const colorValue = Math.max(match.percentage, match.other_percentage)
                      nodesById[one] = {id: one, colorValue}
                      nodesById[other] = {id: other, colorValue}
                  }
              }

              Object.values(nodesById).forEach((node) => {
                  const group = valueToGroup(node.colorValue)
                  if (group in groups) {
                      groups[group] += 1
                  } else {
                      groups[group] = 1
                  }
                  nodes.push({
                      id: node.id,
                      label: node.id,
                      shape: 'dot',
                      group,
                  })
              })

              edges = newMatches.map(match => ({
                  id: match.id,
                  from: match.uniid,
                  to: match.other_uniid,
                  label: `${Math.max(match.percentage, match.other_percentage)}`,
                  width: this.edgeWidth(match.percentage),
              }))


              this.charts.networkChart.chartNodes = nodes
              this.charts.networkChart.chartEdges = edges
          },

          loadGraphs() {
              this.graphsLoaded = true
          },

          showAcceptableNodesSwitch(bool) {
              this.showAcceptableNodes = bool;
              this.parseMatches(this.matches)
          },

          showPlagiarismNodesSwitch(bool) {
              this.showPlagiarismNodes = bool;
              this.parseMatches(this.matches)
          },

          showNewNodesSwitch(bool) {
              this.showNewNodes = bool;
              this.parseMatches(this.matches)
          },

          edgeWidth(percentage) {
              if (percentage > 80) return 5
              else if (percentage > 60) return 4
              else if (percentage > 40) return 3
              else if (percentage > 20) return 2
              else if (percentage > 10) return 1.5
              else return 1
          }
      }
}
</script>

<style scoped>
.graph {
    border-radius: 15px;
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
    background: #f0ffff;
}

.apexGraph {
    padding: 10px;
}
</style>