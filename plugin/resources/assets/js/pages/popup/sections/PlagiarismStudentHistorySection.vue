<template>
    <popup-section
        title="Student plagiarism history"
        subtitle="This section displays information about the student's plagiarism history across multiple Charons"
    >

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchStudentMatches()">{{
                    showStudentHistoryText
                }}
            </v-btn>
        </template>

        <div>
            <v-card-title>
                <v-text-field
                    v-model="search"
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
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
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
        </div>

    </popup-section>
</template>

<script>
import PlagiarismMatchModal from "../partials/PlagiarismMatchModal";
import {PopupSection} from '../layouts';
import {Plagiarism} from "../../../api";
import {mapGetters} from "vuex";

export default {
    components: {PopupSection, PlagiarismMatchModal},
    props: ['student'],

    data() {
        return {
            search: '',
            status: '',
            select: {status: 'All', abbr: ''},
            selectItems: [
                {status: 'All', abbr: ''},
                {status: 'New', abbr: 'new'},
                {status: 'Acceptable', abbr: 'acceptable'},
                {status: 'Plagiarism', abbr: 'plagiarism'},
            ],
            matches: [],
            sortBy: 'created_timestamp',
            sortDesc: false
        }
    },

    computed: {
        headers() {
            return [
                {text: 'Created at', align: 'start', value: 'created_timestamp'},
                {text: 'Lines matched', value: 'lines_matched'},
                {text: 'Uni-ID', value: 'uniid'},
                {text: 'Percentage', value: 'percentage'},
                {text: 'Other Uni-ID', value: 'other_uniid'},
                {text: 'Other Percentage', value: 'other_percentage'},
                {
                    text: 'Status', value: 'status', filter: value => {
                        if (!this.status) return true

                        return value === this.status
                    }
                },
                {text: 'Actions', value: 'actions', sortable: false, filterable: false},
            ]
        },

        showStudentHistoryText() {
            return 'Fetch student matches'
        },

        ...mapGetters([
            'courseId',
        ]),
    },

    methods: {
        updateStatus(match, newStatus) {
            Plagiarism.updateMatchStatus(this.course.id, match.id, newStatus, response => {
                match.status = response.status;
            })
        },

        fetchStudentMatches() {
            Plagiarism.fetchStudentMatches(this.courseId, this.student.username, (response) => {
                this.matches = response
                this.formatMatches(response)
            })
        },

        formatMatches(matchesFromResponse) {
            return matchesFromResponse.map(match => {
                match.created_timestamp = match.created_timestamp.split('.')[0]

                if (match.other_uniid === this.student.username.split('@')[0]) {
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
                }
                return match
            })
        },

        getColor(status) {
            if (status === 'plagiarism') return '#f44336'
            else if (status === 'acceptable') return '#56a576';
            else return '#8e8e8e';
        },
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