<template>
    <popup-section
        title="Plagiarism overview">

        <div style="display: flex; justify-content: space-around;">
            <apexcharts height="500px" width="800px" type="bar" :options="charts.barChart.chartOptions"
                        :series="charts.barChart.series"></apexcharts>

            <apexcharts height="500px" width="500px" type="donut" :options="donutOptions"
                        :series="donutSeries"></apexcharts>
        </div>

    </popup-section>
</template>

<script>
import PopupSection from "../layouts/PopupSection";
import VueApexCharts from "vue-apexcharts";
import {NEUTRAL, INTERESTING, SUSPICIOUS, WARNING, DANGER, valueToGroup} from '../theme'

export default {
    name: "PlagiarismOverviewSection",
    components: {PopupSection, 'apexcharts': VueApexCharts},
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
                        edges: {
                            width: 2,
                        },
                        physics: {
                            stabilization: false,
                        },
                        groups: {
                            [NEUTRAL]: {
                                color: {
                                    border: '#3E7DE2',
                                    background: '#9FC2F7',
                                    highlight: {background: '#8AC3FF', border: '#3E7DE2'},
                                },
                            },
                            [INTERESTING]: {
                                color: {
                                    border: '#302CAB',
                                    background: '#6B72F4',
                                    highlight: {background: '#6970F4', border: '#302CAB'},
                                },
                            },
                            [SUSPICIOUS]: {
                                color: {
                                    border: '#F3A83B',
                                    background: '#F8F652',
                                    highlight: {background: '#DAD84C', border: '#F3A83B'},
                                },
                            },
                            [WARNING]: {
                                color: {
                                    border: '#BA812C',
                                    background: '#F4AB3E',
                                    highlight: {background: '#FAAE41', border: '#BA812C'},
                                },
                            },
                            [DANGER]: {
                                color: {
                                    border: '#E43428',
                                    background: '#EC8584',
                                    highlight: {background: '#F54137', border: '#E43428'},
                                },
                            },
                        },
                    }
                }
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
            const nodes = []
            const groups = {[NEUTRAL]: 0, [INTERESTING]: 0, [SUSPICIOUS]: 0, [WARNING]: 0, [DANGER]: 0}

            if (this.matches) {
                const nodesById = {}

                for (let i = 0; i < this.matches.length; i++) {
                    const match = this.matches[i]

                    const oneSubmission = match.submission
                    const otherSubmission = match.other_submission

                    const one = oneSubmission.gitlab_project.owner.uniid
                    const other = otherSubmission.gitlab_project.owner.uniid

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
                    });
                });
            }

            return nodes
        },

        networkEdges() {
            let links = []

            if (this.matches) {
                links = this.matches.map(match => ({
                    from: match.submission.gitlab_project.owner.uniid,
                    to: match.other_submission.gitlab_project.owner.uniid,
                    label: `${Math.max(match.percentage, match.other_percentage)}`,
                }))
            }

            return links
        },
    },

    methods: {
        parseMatches(newMatches) {
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
        }
    }
}
</script>

<style scoped>

</style>