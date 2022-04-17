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
                Filter both percentage fields:
                <toggle-button
                    :buttonDefault="percentageButtonClicked"
                    @buttonClicked="clickPercentageButton($event)"
                ></toggle-button>
                <min-max-slider
                    @minMaxChanged="passMinMaxValues"
                    :text="'Percentage'"
                    style="width:40%;padding-left: 30px"
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
            timeId: '',
            selectedHistory: '',
            historyTimes: [],
            min_percentage: 0,
            max_percentage: 100,
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

            this.checkFiltration()

            Plagiarism.fetchMatches(
                this.charon.id,
                this.min_percentage,
                this.max_percentage,
                this.percentageButtonClicked,
                response => {
                    this.matches = response['matches'];
                    let times = response['times'];
                    times.forEach(timeObj => timeObj.created_timestamp = new Date(timeObj.created_timestamp).toLocaleString());
                    this.historyTimes = times;
                    this.selectedHistory = null;

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

            this.checkFiltration()

            Plagiarism.fetchMatchesByRun(
                run.id, this.charon.id,
                this.min_percentage,
                this.max_percentage,
                this.percentageButtonClicked,
                response => {
                    this.matches = response;
                    this.$emit('matchesFetched', response)
                }
            )
        },

        passMinMaxValues(minValue, maxValue) {
            this.min_percentage = minValue
            this.max_percentage = maxValue
            this.fetchMatchesAll()
        },

        clickPercentageButton(event) {
            this.percentageButtonClicked = event
            this.fetchMatchesAll()
        },

        fetchMatchesAll() {
            if (this.selectedHistory) {
                this.getMatchesByPlagiarismRun(this.selectedHistory)
            } else {
                this.fetchMatches()
            }
        },

        checkFiltration() {
            if (this.min_percentage !== 0 || this.max_percentage !== 100) {
                this.$emit('filtrationOn', true)
            } else {
                this.$emit('filtrationOn', false)
            }
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
