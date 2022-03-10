<template>
    <popup-section
        :title="this.sectionTitle"
    >

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked">Run plagiarism check</v-btn>
            <toggle-button @buttonClicked="showHistoryTable($event)"></toggle-button>
        </template>
        <div>
            <div v-if="!toggleShowHistoryTable">
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left">
                                    Charon
                                </th>
                                <th class="text-left">
                                    Created at
                                </th>
                                <th class="text-left">
                                    Updated at
                                </th>
                                <th class="text-left">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    {{latestCheck.charonName}}
                                </td>
                                <td>
                                    {{latestCheck.created_at}}
                                </td>
                                <td>
                                    {{latestCheck.updated_at}}
                                </td>
                                <td>
                                    {{latestCheck.status}}
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </div>
            <div v-if="toggleShowHistoryTable">
                <v-data-table
                :headers="headers"
                :items="checkHistory"
                :items-per-page="5"
                class="elevation-1">
                </v-data-table>
            </div>
        </div>

    </popup-section>
</template>

<script>
import {mapState} from 'vuex'

import {PopupSection} from '../layouts';
import ToggleButton from "../../../components/partials/ToggleButton";
import {Plagiarism} from "../../../api";

export default {
    name: 'plagiarism-check-history-section',

    components: {ToggleButton,PopupSection},

    data() {
        return {
            sectionTitle: 'Latest check',
            checkHistory: [{"charonName":"PR02_primes","created_at":"2022-03-07T14:49:59.000000Z","updated_at":"2022-03-07T14:50:00.000000Z","status":"Check started.","checkId":47},

                {"charonName":"PR02_primes","created_at":"2022-03-07T14:49:03.000000Z","updated_at":"2022-03-07T14:49:03.000000Z","status":"Check started.","checkId":46},

                {"charonName":"EX11_Order","created_at":"2022-03-02T12:23:36.000000Z","updated_at":"2022-03-02T12:23:53.000000Z","status":"Check successful","checkId":39}],
            toggleShowHistoryTable: false,
            latestCheck: [],
            headers: [
                {text: 'Charon', align: 'start', value: 'charonName'},
                {text: 'Created at', value: 'created_at'},
                {text: 'Updated at', value: 'updated_at'},
                {text: 'Status', value: 'status'},
            ]
        }
    },

    computed: {
        ...mapState([
            'charon',
            'course'
        ]),
    },
    methods: {
        handleRunPlagiarismClicked() {
            Plagiarism.runPlagiarismCheck(this.course.id, this.charon.id, response => {
                this.latestCheck = response.status
                window.VueEvent.$emit(
                    'show-notification',
                    response.message,
                    'success',
                )
                console.log(this.latestCheck)
            })
            this.refreshLatestStatus();
        },
        showHistoryTable(bool) {
            this.toggleShowHistoryTable = bool;
            if (this.toggleShowHistoryTable === true) {
                this.sectionTitle = "History of checks";
                //Plagiarism.getCheckHistory()
            } else {
                this.sectionTitle = "Latest check";
            }
        },
        refreshLatestStatus() {
            this.interval = setInterval(this.getLatestStatus, 5000)
        },
        getLatestStatus() {
            Plagiarism.getLatestCheckStatus(this.charon.id, this.latestCheck.checkId, response => {
                this.latestCheck = response
                if (this.latestCheck.status === "Check successful") {
                    clearInterval(this.interval)
                }
            })
        }
    },

    beforeDestroy () {
        clearInterval(this.interval)
    },
}
</script>
