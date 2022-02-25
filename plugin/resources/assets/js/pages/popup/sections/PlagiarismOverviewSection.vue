<template>
    <popup-section
        title="Plagiarism overview">

        <div style="display: flex; justify-content: space-around;">
            <apexcharts height="500px" width="800px" type="bar" :options="barOptions" :series="barSeries"></apexcharts>

            <apexcharts height="500px" width="500px" type="donut" :options="donutOptions" :series="donutSeries"></apexcharts>
        </div>

    </popup-section>
</template>

<script>
import PopupSection from "../layouts/PopupSection";
import VueApexCharts from "vue-apexcharts";

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
            }
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

            this.charts.barChart.series[0].data = Object.values(categories)
            this.charts.donutChart.series = Object.values(labels)
        }
    }
}
</script>

<style scoped>

</style>