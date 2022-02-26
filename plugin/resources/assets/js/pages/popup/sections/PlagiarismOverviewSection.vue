<template>
    <popup-section
        title="Plagiarism overview">

        <div style="display: flex; justify-content: space-around;">
            <apexcharts height="500px" width="800px" type="bar" :options="charts.barChart.chartOptions"
                        :series="charts.barChart.series"></apexcharts>

            <apexcharts height="500px" width="500px" type="donut" :options="donutOptions"
                        :series="donutSeries"></apexcharts>

            <d3-network
                :net-nodes="networkNodes"
                :net-links="networkLinks"
                :options="networkOptions"></d3-network>
        </div>

    </popup-section>
</template>

<script>
import PopupSection from "../layouts/PopupSection";
import VueApexCharts from "vue-apexcharts";
import D3Network from 'vue-d3-network'

export default {
    name: "PlagiarismOverviewSection",
    components: {PopupSection, 'apexcharts': VueApexCharts, 'd3-network': D3Network},
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
                    chartOptions: {
                        force: 500,
                        nodeSize: 15,
                        nodeLabels: true,
                        linkLabels: true,
                        linkWidth: 5,
                        size: {
                            w: 500,
                            h: 500
                        }
                    },
                    chartNodes: [],
                    chartLinks: []
                },
            },
        }
    },

    watch: {
        matches: function (newMatches, oldMatches) {
            this.parseMatches(newMatches)
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

        networkOptions() {
            return this.charts.networkChart.chartOptions
        },

        networkNodes() {
            return this.charts.networkChart.chartNodes
        },

        networkLinks() {
            return this.charts.networkChart.chartLinks
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
            const links = []
            const nodeIds = {}

            for (let i = 0; i < newMatches.length; i++) {
                const match = newMatches[i]
                const percentage = Math.max(match.percentage, match.other_percentage)

                const uniid = match.uniid
                const other_uniid = match.other_uniid

                if (!(uniid in nodeIds)) {
                    const nodeValues = Object.values(nodeIds)
                    nodeIds[uniid] = nodeValues.length ? Math.max(...nodeValues) + 1 : 0

                    const firstNode = {
                        id: nodeIds[uniid],
                        name: uniid,
                    }
                    nodes.push(firstNode)
                }

                if (!(other_uniid in nodeIds)) {
                    const nodeValues = Object.values(nodeIds)
                    nodeIds[other_uniid] = nodeValues.length ? Math.max(...nodeValues) + 1 : 0

                    const secondNode = {
                        id: nodeIds[other_uniid],
                        name: other_uniid,
                    }
                    nodes.push(secondNode)
                }

                const link = {
                    sid: nodeIds[uniid],
                    tid: nodeIds[other_uniid],
                    _color: this.colorByPercentage(percentage)
                }
                links.push(link)
            }

            this.charts.networkChart.chartNodes = nodes
            this.charts.networkChart.chartLinks = links
        },

        colorByPercentage(percentage) {
            if (percentage < 20) {
                return '#9FC2F7'
            } else if (percentage < 40) {
                return '#6B72F4'
            } else if (percentage < 60) {
                return '#F8F652'
            } else if (percentage < 80) {
                return '#F4AB3E'
            } else {
                return '#EC8584'
            }
        },
    }
}
</script>

<style scoped src="vue-d3-network/dist/vue-d3-network.css">

</style>