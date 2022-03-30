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
                    style="width:80%;float: left;padding-right: 10px"
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
                        <plagiarism-match-modal :match="item" :color="getColor(item.status)"></plagiarism-match-modal>
                        <v-btn class="accepted-button" v-if="item.status !== 'acceptable'" @click="updateStatus(item, 'acceptable')" icon>
                            <v-icon aria-label="Accepted" role="button" aria-hidden="false">mdi-thumb-up-outline</v-icon>
                        </v-btn>
                        <v-btn class="plagiarism-button" v-if="item.status !== 'plagiarism'" @click="updateStatus(item, 'plagiarism')" icon>
                            <v-icon aria-label="Plagiarism" role="button" aria-hidden="false">mdi-thumb-down-outline</v-icon>
                        </v-btn>
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

export default {
    name: 'plagiarism-matches-section',

    components: {PlagiarismMatchModal, PopupSection, CharonSelect, PlagiarismSimilaritiesTabs},

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
            matches: []
        }
    },

    computed: {
        headers () {
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
                match.status = response.status;
            })
        },

        fetchMatches() {
            if (!this.charon) return;

            Plagiarism.fetchMatches(this.course.id, this.charon.id, response => {
                this.matches = response;
                this.$emit('matchesFetched', response)
            })
        },

        arrayRemove(arr, value) {
            return arr.filter(function(ele) {
                return ele !== value;
            });
        },

        getColor(status) {
            if (status === 'plagiarism') return '#f44336'
            else if (status === 'acceptable') return '#56a576';
            else return '#8e8e8e';
        },
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
