<template>
    <popup-section
        title="Plagiarism matches"
    >
        <template slot="header-right">
            <charon-select/>
            <v-btn @click="fetchMatches()">Fetch Matches</v-btn>
        </template>

        <div>
            <v-card-title>
                <v-text-field
                    v-model="search"
                    append-icon="mdi-magnify"
                    label="Search"
                    style="width:50%;float: left;padding-right: 10px"
                ></v-text-field>
                <v-select
                    @change="getMatchesByPlagiarismRun($event)"
                    :items="historyTimes"
                    label="Run times"
                    v-model="selectedHistory"
                    item-text="created_timestamp"
                    item-value="id"
                    return-object
                    style="width:35%;padding-left: 10px"
                ></v-select>
                <v-select
                    v-model="status"
                    :items="selectItems"
                    item-text="status"
                    item-value="abbr"
                    label="Status"
                    style="width:15%;padding-left: 10px"
                ></v-select>
            </v-card-title>
            <v-card-title>
                <div style="width:10%">
                    <toggle-button
                        :buttonDefault="percentageButtonClicked"
                        @buttonClicked="clickPercentageButton($event)"
                    ></toggle-button>
                </div>
                <div style="width:50%">
                    Filter percentage for:
                    <span v-if="percentageButtonClicked">both</span>
                    <span v-else>at least one</span>
                </div>
                <min-max-slider
                    @minMaxChanged="passMinMaxValues"
                    :text="'Percentage'"
                    style="width:40%"
                ></min-max-slider>
            </v-card-title>
        </div>

        <div>
            <v-data-table
                class="center-table"
                :headers="headers"
                :items="matches"
                :search="search"
                :footer-props="{
                    'items-per-page-options': [10, 25, 50, -1]
                }"
                :items-per-page="25"
            >
                <template v-slot:item.status="{ item }">
                    <v-chip v-bind:class="item.status === 'acceptable' ? 'accepted-button': item.status === 'plagiarism' ? 'plagiarism-button' : ''">
                        {{item.status}}
                    </v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-row>
                        <plagiarism-match-modal :match="item"></plagiarism-match-modal>
                        <div v-if="!selectedHistory">
                            <v-btn class="accepted-button" v-if="item.status !== 'acceptable'" @click="updateStatus(item, 'acceptable')" icon>
                                <v-icon aria-label="Accepted" role="button" aria-hidden="false">mdi-thumb-up-outline</v-icon>
                            </v-btn>
                            <v-btn class="plagiarism-button" v-if="item.status !== 'plagiarism'" @click="updateStatus(item, 'plagiarism')" icon>
                                <v-icon aria-label="Plagiarism" role="button" aria-hidden="false">mdi-thumb-down-outline</v-icon>
                            </v-btn>
                        </div>
                    </v-row>
                </template>
            </v-data-table>
        </div>

    </popup-section>
</template>

<script>
import {mapState} from 'vuex'

import {PopupSection} from '../layouts'
import {CharonSelect, PlagiarismSimilaritiesTabs} from '../partials'
import {Plagiarism} from '../../../api'
import PlagiarismMatchModal from "../partials/PlagiarismMatchModal";
import MinMaxSlider from "../../../components/partials/MinMaxSlider";
import ToggleButton from "../../../components/partials/ToggleButton";

export default {
    name: 'plagiarism-matches-section',

    components: {PlagiarismMatchModal, PopupSection, CharonSelect, PlagiarismSimilaritiesTabs,
      MinMaxSlider, ToggleButton},

    data() {
        return {
            search: '',
            status: '',
            select: { status: 'All', abbr: ''},
            selectItems: [
                { status: 'All', abbr: ''},
                { status: 'New', abbr: 'new'},
                { status: 'Acceptable', abbr: 'acceptable'},
                { status: 'Plagiarism', abbr: 'plagiarism'},
            ],
            matches: [],
            allMatches: [],
            timeId: '',
            selectedHistory: '',
            historyTimes: [],
            minPercentage: 0,
            maxPercentage: 100,
            percentageButtonClicked: true,
        }
    },

    computed: {
        headers() {
            return [
                {text: 'Lines matched', align: 'start', value: 'lines_matched'},
                {text: 'Uni-ID', value: 'uniid'},
                {text: 'Percentage', value: 'percentage'},
                {text: 'Other Uni-ID', value: 'other_uniid'},
                {text: 'Other Percentage', value: 'other_percentage'},
                {text: 'Status', value: 'status', filter: value => {
                        if (!this.status) return true

                        return value === this.status
                    }},
                {text: 'Actions', value: 'actions', sortable: false, filterable: false},
            ]
        },
        ...mapState([
            'charon',
            'course'
        ]),
    },

    methods: {
        updateStatus(match, newStatus) {
            Plagiarism.updateMatchStatus(this.course.id, match.id, newStatus, response => {
                match.status = response.status
                VueEvent.$emit('refresh-plagiarism-overview')
            })
        },

        fetchMatches() {
            if (!this.charon) return;

            Plagiarism.fetchMatches(
                this.charon.id,
                response => {
                    this.allMatches = response['matches'];
                    this.matches = response['matches'];
                    let times = response['times'];
                    times.forEach(timeObj => timeObj.created_timestamp = new Date(timeObj.created_timestamp).toLocaleString());
                    this.historyTimes = times;
                    this.selectedHistory = null;
                    this.filterMatchesByPercentage()
                    this.$emit('matchesFetched', response['matches'])
                }
            )
        },

        arrayRemove(arr, value) {
            return arr.filter(function(ele) {
                return ele !== value;
            });
        },

        getMatchesByPlagiarismRun(run) {
            this.selectedHistory = run

            Plagiarism.fetchMatchesByRun(
                run.id, this.charon.id,
                response => {
                    this.allMatches = response;
                    this.matches = response;
                    this.$emit('matchesFetched', response)
                    this.filterMatchesByPercentage()
                }
            )
        },

        passMinMaxValues(minValue, maxValue) {
            this.minPercentage = minValue
            this.maxPercentage = maxValue
            this.filterMatchesByPercentage()
        },

        clickPercentageButton(event) {
            this.percentageButtonClicked = event
            this.filterMatchesByPercentage()
        },

        filterMatchesByPercentage() {
            this.matches = []
            const minPercentage = this.minPercentage
            const maxPercentage = this.maxPercentage
            const button = this.percentageButtonClicked
            this.allMatches.forEach(match => {
                if ((button && match.percentage >= minPercentage && match.percentage <= maxPercentage && match.other_percentage >= minPercentage && match.other_percentage <= maxPercentage) ||
                    !button && ((match.percentage >= minPercentage && match.percentage <= maxPercentage) || (match.other_percentage >= minPercentage && match.other_percentage <= maxPercentage))) {
                    this.matches.push(match)
                }
            })
        }
    },
}
</script>

<style>
.plagiarism-button {
    background-color: #f44336 !important;
}
.accepted-button {
    background-color: #56a576 !important;
}
.center-table table td{
    vertical-align: middle;
}
.center-table table th{
    vertical-align: middle;
}
</style>
