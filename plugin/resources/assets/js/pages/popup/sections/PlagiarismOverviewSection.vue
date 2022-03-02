<template>
    <popup-section
        title="Plagiarism overview">

        <div style="display: flex; justify-content: space-around;">
            <apexcharts height="500px" width="800px" type="bar" :options="charts.barChart.chartOptions"
                        :series="charts.barChart.series"></apexcharts>

            <apexcharts height="500px" width="500px" type="donut" :options="donutOptions"
                        :series="donutSeries"></apexcharts>

            <VisNetwork style="height: 400px; width: 400px" :nodes="networkNodes" :edges="networkEdges"></VisNetwork>
        </div>

    </popup-section>
</template>

<script>
import PopupSection from "../layouts/PopupSection";
import VueApexCharts from "vue-apexcharts";
import D3Network from 'vue-d3-network';
import VisNetwork from "./VisNetwork";
import {NEUTRAL, INTERESTING, SUSPICIOUS, WARNING, DANGER, valueToGroup} from '../../../helpers/PlagiarismColors';

export default {
    name: "PlagiarismOverviewSection",
    components: {PopupSection, 'apexcharts': VueApexCharts, 'd3-network': D3Network, VisNetwork},
    props: ['matches'],
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
                let averagePercentage = (match.percentage + match.other_percentage) / 2
                let status = match.status
                for (let category in categories) {
                    let range = category.split('-')
                    let lower = parseInt(range[0])
                    let upper = parseInt(range[1])
                    if (averagePercentage > lower && averagePercentage < upper) {
                        categories[category] += 1
                    }
                }

                labels[status] = labels[status] + 1
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

                const one = match.uniid
                const other = match.other_uniid

                const colorValue = Math.max(match.percentage, match.other_percentage)
                nodesById[one] = {id: one, colorValue}
                nodesById[other] = {id: other, colorValue}
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
                from: match.uniid,
                to: match.other_uniid,
                label: `${Math.max(match.percentage, match.other_percentage)}`,
            }))


            this.charts.networkChart.chartNodes = nodes
            this.charts.networkChart.chartEdges = edges
        }
    }
}
</script>

<style scoped src="vue-d3-network/dist/vue-d3-network.css">

</style>