<template>
    <popup-section
        title="Plagiarism matches"
    >

        <template slot="header-right">
            <charon-select/>
            <popup-select
                name="match"
                :options="status_items"
                value-key="status"
                placeholder-key="status"
                size="medium"
                v-model="select"
            />
            <v-btn @click="fetchMatches()">Fetch Matches</v-btn>
        </template>
        <div>
            <v-text-field
                v-model="search"
                append-icon="mdi-magnify"
                label="Search"
                single-line
                hide-details
            ></v-text-field>
            <v-data-table
                class="center-table"
                :headers="headers"
                :items="matches"
                :search="search">
                <template v-slot:item.actions="{ item }">
                    <v-row>
                        <plagiarism-match-modal :match="item"></plagiarism-match-modal>
                        <plagiarism-toggle :chosenStatus="chosenStatus(item.id)" :originalStatus="item.status" :matchId="item.id"></plagiarism-toggle>
                    </v-row>
                </template>
                <template v-slot:body.append>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <v-btn @click="updateStatuses()">Confirm</v-btn>
                        </td>
                    </tr>
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
import PopupSelect from "../partials/PopupSelect";
import PlagiarismToggle from "../../../components/partials/PlagiarismToggle";

export default {
    name: 'plagiarism-matches-section',

    components: {PlagiarismMatchModal, PopupSection, CharonSelect, PlagiarismSimilaritiesTabs, PopupSelect, PlagiarismToggle},

    data() {
        return {
            addAccepted: [],
            addPlagiarism: [],
            select: 'New',
            status_items: [
                {status: 'New'},
                {status: 'Plagiarism'},
                {status: 'Accepted'}
            ],
            search: "",
            matches: [],
            headers: [
                {text: 'Matches', align: 'start', value: 'lines_matched'},
                {text: 'Uni-ID', value: 'uniid'},
                {text: 'Percentage', value: 'percentage'},
                {text: 'Other Uni-ID', value: 'other_uniid'},
                {text: 'Other Percentage', value: 'other_percentage'},
                {text: 'Status', value: 'status'},
                {text: 'Actions', value: 'actions', sortable: false, filterable: false},
            ]
        }
    },

    computed: {
        ...mapState([
            'charon',
            'course'
        ]),

    },

    mounted() {
        VueEvent.$on('updateMatchStatus', (matchId, status) => {
            this.updateStatus(matchId, status)
        });
    },

    methods: {
        chosenStatus(matchId) {
            if (this.addAccepted.includes(matchId)) {
                return 0;
            }
            if (this.addPlagiarism.includes(matchId)) {
                return 1;
            }

            return undefined;
        },
        updateStatus(matchId, status) {
            let accepted = this.addAccepted
            let plagiarism = this.addPlagiarism
            if (plagiarism.includes(matchId)) {
                plagiarism = this.arrayRemove(plagiarism, matchId);
                if (status === "acceptable") {
                    accepted.push(matchId)
                }
            } else if (accepted.includes(matchId)) {
                accepted = this.arrayRemove(accepted, matchId);
                if (status === "plagiarism") {
                    plagiarism.push(matchId)
                }
            } else {
                if (status === "acceptable") {
                    accepted.push(matchId);
                } else {
                    plagiarism.push(matchId)
                }
            }
            this.addAccepted = accepted;
            this.addPlagiarism = plagiarism;
        },
        updateStatuses() {
            console.log(this.addAccepted)
            console.log(this.addPlagiarism)
        },
        fetchMatches() {
            if (!this.charon) return

            Plagiarism.fetchMatches(this.course.id, this.charon.id, response => {
                this.matches = response
            })
        },

        arrayRemove(arr, value) {
            return arr.filter(function(ele) {
                return ele !== value;
            });
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
