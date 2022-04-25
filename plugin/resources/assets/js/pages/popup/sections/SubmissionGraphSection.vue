<template>
    <popup-section title="Submissions graphs" subtitle="Graphs showing the number of submissions data">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchSubmissions">Load Data</v-btn>
        </template>

        <div v-if="value">
            <v-row>
                <v-col cols="12" md="6">
                    <span>{{ graphTitleEveryDay }}</span>
                    <apexcharts type="line" :options="graphSubmissionsEveryDayOptions"
                                :series="graphSubmissionsEveryDaySeries" ref="chartEveryDay"></apexcharts>
                </v-col>

                <v-col cols="12" md="6">
                    <span>{{ graphTitleToday }}</span>
                    <apexcharts type="line" :options="graphSubmissionsTodayOptions"
                                :series="graphSubmissionsTodaySeries" ref="chartToday"></apexcharts>
                </v-col>
            </v-row>
        </div>

        <div v-else>
            {{ empty }}
        </div>

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

    deactivated() {
        this.$refs.chartEveryDay.$destroy()
        this.$refs.chartToday.$destroy()
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
        graphSubmissionsEveryDayOptions() {
            return {
                xaxis: {
                    categories: this.graphDataEveryDay.map(sub => sub.dateRow)
                },
                chart: {
                    width: "100%",
                    height: 400
                }
            }
        },

        graphSubmissionsEveryDaySeries() {
            return [{
                name: 'submissions',
                data: this.graphDataEveryDay.map(sub => sub.count)
            }]
        },

        graphSubmissionsTodayOptions() {
            return {
                xaxis: {
                    categories: this.graphDataToday.map(sub => sub.time.slice(0, sub.time.lastIndexOf(":")))
                },
                chart: {
                    width: "100%",
                    height: 400
                }
            }
        },

        graphSubmissionsTodaySeries() {
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