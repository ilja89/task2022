<template>
    <popup-section
        title="Student plagiarism history"
        subtitle="This section displays information about the student's plagiarism history across multiple Charons"
    >

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchStudentMatchesClicked()">{{
                    showStudentHistoryText
                }}
            </v-btn>
        </template>

        <v-card v-if="activeMatchesFetched">
            <v-switch
                class="pa-4"
                v-model="showAllHistory"
                :label="showAllHistoryLabel"
                @change="showAllHistoryToggled"
            ></v-switch>
            <v-card-title>
                <v-text-field
                    v-model="searchMatches"
                    append-icon="mdi-magnify"
                    label="Search"
                    style="width:60%;float: left;padding-right: 10px"
                ></v-text-field>

                <v-select
                    v-model="status"
                    :items="selectItems"
                    item-text="status"
                    item-value="abbr"
                    label="Status"
                    style="width:10%;padding-left: 10px"
                ></v-select>
            </v-card-title>
            <v-data-table
                class="center-table"
                :headers="headersMatches"
                :items="getMatches"
                :search="searchMatches"
                :footer-props="{
                    'items-per-page-options': [10, 25, 50, -1]
                }"
                :items-per-page="10"
                :sort-by.sync="sortMatches"
                :sort-desc.sync="sortMatchesDesc"
            >
                <template v-slot:item.status="{ item }">
                    <v-chip
                        v-bind:class="item.status === 'acceptable' ? 'accepted-button': item.status === 'plagiarism' ? 'plagiarism-button' : ''">
                        {{ item.status }}
                    </v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-row>
                        <plagiarism-match-modal :match="item" :color="getColor(item.status)"></plagiarism-match-modal>
                        <div>
                            <v-btn class="accepted-button" v-if="item.status !== 'acceptable'"
                                   @click="updateStatus(item, 'acceptable')" icon>
                                <v-icon aria-label="Accepted" role="button" aria-hidden="false">mdi-thumb-up-outline
                                </v-icon>
                            </v-btn>
                            <v-btn class="plagiarism-button" v-if="item.status !== 'plagiarism'"
                                   @click="updateStatus(item, 'plagiarism')" icon>
                                <v-icon aria-label="Plagiarism" role="button" aria-hidden="false">
                                    mdi-thumb-down-outline
                                </v-icon>
                            </v-btn>
                        </div>
                    </v-row>
                </template>
            </v-data-table>
        </v-card>

        <v-card v-if="activeMatchesFetched" class="mt-16">
            <v-data-table
                class="center-table"
                :headers="headersStatistics"
                :items="statistics"
                :footer-props="{
                    'items-per-page-options': [10, 25, 50, -1]
                }"
                :items-per-page="10"
                :sort-by.sync="sortForStatistics"
                :sort-desc.sync="sortDescForStatistics"
            ></v-data-table>
        </v-card>

        <v-card class="mt-16">
            <apexcharts type="bar" height="500" :options="chartOptions" :series="chartSeries"
                        v-show="activeMatchesFetched"></apexcharts>
        </v-card>

    </popup-section>
</template>

<script>
import PlagiarismMatchModal from "../partials/PlagiarismMatchModal";
import {PopupSection} from '../layouts';
import {Plagiarism} from "../../../api";
import {mapGetters} from "vuex";
import VueApexCharts from 'vue-apexcharts';

export default {
    components: {PopupSection, PlagiarismMatchModal, 'apexcharts': VueApexCharts},
    props: ['student'],

    data() {
        return {
            searchMatches: '',
            status: '',
            select: {status: 'All', abbr: ''},
            selectItems: [
                {status: 'All', abbr: ''},
                {status: 'New', abbr: 'new'},
                {status: 'Acceptable', abbr: 'acceptable'},
                {status: 'Plagiarism', abbr: 'plagiarism'},
            ],
            allMatches: [],
            activeMatches: [],
            sortMatches: 'created_timestamp',
            sortForStatistics: 'max_lines_matched',
            sortMatchesDesc: false,
            sortDescForStatistics: true,
            activeMatchesFetched: false,
            inactiveMatchesFetched: false,
            showAllHistory: false,
            showAllHistoryLabel: 'Show all history'
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        headersMatches() {
            return [
                {text: 'Created at', align: 'start', value: 'created_timestamp'},
                {text: '', align: 'center', value: 'active'},
                {text: 'Charon', align: 'center', value: 'assignment_name'},
                {text: 'Lines matched', align: 'center', value: 'lines_matched'},
                {text: 'Uni-ID', align: 'center', value: 'uniid'},
                {text: 'Percentage', align: 'center', value: 'percentage'},
                {text: 'Other Uni-ID', align: 'center', value: 'other_uniid'},
                {text: 'Other Percentage', align: 'center', value: 'other_percentage'},
                {
                    text: 'Status', value: 'status', filter: value => {
                        if (!this.status) return true

                        return value === this.status
                    }
                },
                {text: 'Actions', value: 'actions', sortable: false, filterable: false},
            ]
        },

        headersStatistics() {
            return [
                {text: 'Charon', align: 'start', value: 'assignment_name'},
                {text: 'Max lines matched', align: 'center', value: 'max_lines_matched'},
                {text: 'Max percentage', align: 'center', value: 'max_percentage'},
                {text: 'Max other percentage', align: 'center', value: 'max_other_percentage'},
                {text: 'New amount', align: 'center', value: 'new_amount'},
                {text: 'Acceptable amount', align: 'center', value: 'acceptable_amount'},
                {text: 'Plagiarism amount', align: 'center', value: 'plagiarism_amount'}
            ]
        },

        showStudentHistoryText() {
            return 'Fetch student matches'
        },

        chartOptions() {
            return {
                chart: {
                    type: 'bar',
                    height: 500,
                    stacked: true,
                    toolbar: {
                        show: true
                    },
                    zoom: {
                        enabled: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 10
                    },
                },
                title: {
                    text: "Student's plagiarism progress for each tested Charon",
                    align: 'left'
                },
                xaxis: {
                    type: 'category',
                    categories: this.statistics.map(obj => {
                        return obj.assignment_name
                    }),
                },
                legend: {
                    position: 'right',
                    offsetY: 40
                },
                fill: {
                    opacity: 1
                },
                colors: ['#8e8e8e', '#56a576', '#f44336']
            }
        },

        chartSeries() {
            return [
                {
                    name: 'new',
                    data: this.statistics.map(charon_statistics => {
                        return charon_statistics.new_amount
                    })
                }, {
                    name: 'acceptable',
                    data: this.statistics.map(charon_statistics => {
                        return charon_statistics.acceptable_amount
                    })
                }, {
                    name: 'plagiarism',
                    data: this.statistics.map(charon_statistics => {
                        return charon_statistics.plagiarism_amount
                    })
                }
            ]
        },

        getMatches() {
            if (this.showAllHistory) {
                return this.allMatches
            }
            return this.activeMatches
        },

        statistics() {
            return this.calculateStatistics(this.getMatches)
        },
    },

    methods: {
        updateStatus(match, newStatus) {
            Plagiarism.updateMatchStatus(this.courseId, match.id, newStatus, response => {
                match.status = response.status;
            })
        },

        calculateStatistics(matches) {
            let matchesStatistics = {}
            matches.map(match => {
                this.addToStatistics(matchesStatistics, match)
            })
            return Object.values(matchesStatistics)
        },

        fetchStudentActiveMatches() {
            Plagiarism.fetchStudentActiveMatches(this.courseId, this.student.username, (response) => {
                this.activeMatches = response
                if (response.length) {
                    let preparedData = this.prepareData(response)
                    this.allMatches.push(...preparedData)
                    this.activeMatchesFetched = true
                }
            })
        },

        fetchStudentInactiveMatches() {
            Plagiarism.fetchStudentInactiveMatches(this.courseId, this.student.username, (response) => {
                if (response.length) {
                    this.allMatches.push(...response)
                    this.prepareData(this.allMatches)
                    this.inactiveMatchesFetched = true
                }
            })
        },

        swapStudents(match) {
            [
                match.code,
                match.commit_hash,
                match.gitlab_commit_at,
                match.percentage,
                match.submission,
                match.uniid,
                match.other_code,
                match.other_commit_hash,
                match.other_gitlab_commit_at,
                match.other_percentage,
                match.other_submission,
                match.other_uniid
            ] =
                [
                    match.other_code,
                    match.other_commit_hash,
                    match.other_gitlab_commit_at,
                    match.other_percentage,
                    match.other_submission,
                    match.other_uniid,
                    match.code,
                    match.commit_hash,
                    match.gitlab_commit_at,
                    match.percentage,
                    match.submission,
                    match.uniid,
                ]
        },

        addToStatistics(matchesStatistics, match) {
            let charon_name = match.assignment_name
            if (charon_name in matchesStatistics) {
                if (match.lines_matched > matchesStatistics[charon_name]["max_lines_matched"]) {
                    matchesStatistics[charon_name]["max_lines_matched"] = match.lines_matched
                }
                if (match.percentage > matchesStatistics[charon_name]["max_percentage"]) {
                    matchesStatistics[charon_name]["max_percentage"] = match.percentage
                }
                if (match.other_percentage > matchesStatistics[charon_name]["other_percentage"]) {
                    matchesStatistics[charon_name]["max_other_percentage"] = match.other_percentage
                }
                if (match.status === "new") {
                    matchesStatistics[charon_name]["new_amount"] = matchesStatistics[charon_name]["new_amount"] + 1
                }
                if (match.status === "acceptable") {
                    matchesStatistics[charon_name]["acceptable_amount"] = matchesStatistics[charon_name]["acceptable_amount"] + 1
                }
                if (match.status === "plagiarism") {
                    matchesStatistics[charon_name]["plagiarism_amount"] = matchesStatistics[charon_name]["plagiarism_amount"] + 1
                }
            } else {
                matchesStatistics[charon_name] = {
                    "assignment_name": match.assignment_name,
                    "max_lines_matched": match.lines_matched,
                    "max_percentage": match.percentage,
                    "max_other_percentage": match.other_percentage,
                    "new_amount": (match.status === "new") ? 1 : 0,
                    "acceptable_amount": (match.status === "acceptable") ? 1 : 0,
                    "plagiarism_amount": (match.status === "plagiarism") ? 1 : 0
                }
            }
        },

        prepareData(matches) {
            matches.map(match => {
                match.created_timestamp = match.created_timestamp.split('.')[0]

                if (match.other_uniid === this.student.username.split('@')[0]) {
                    this.swapStudents(match)
                }
                return match
            })
            return matches
        },

        getColor(status) {
            if (status === 'plagiarism') return '#f44336'
            else if (status === 'acceptable') return '#56a576';
            else return '#8e8e8e';
        },

        showAllHistoryToggled(historyToggled) {
            if (historyToggled && !this.inactiveMatchesFetched) {
                this.fetchStudentInactiveMatches()
            }
        },

        fetchStudentMatchesClicked() {
            if (!this.activeMatchesFetched) {
                this.fetchStudentActiveMatches()
            }
        }
    }
}
</script>

<style>
.plagiarism-button {
    background-color: #f44336 !important;
}

.accepted-button {
    background-color: #56a576 !important;
}

.center-table table td {
    vertical-align: middle;
}

.center-table table th {
    vertical-align: middle;
}
</style>