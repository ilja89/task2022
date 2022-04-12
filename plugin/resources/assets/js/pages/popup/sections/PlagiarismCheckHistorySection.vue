<template>
    <popup-section
        :title="this.sectionTitle"
    >

        <template slot="header-right">
            <charon-select></charon-select>
            <v-btn class="ma-2" tile outlined color="primary" @click="handleRunPlagiarismClicked" :disabled="submitDisabled">Run plagiarism check</v-btn>
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
                                    Author
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
                                    {{latestCheck.author}}
                                </td>
                                <td>
                                    {{latestCheck.created_at}}
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
import CharonSelect from "../partials/CharonSelect";

export default {
    name: 'plagiarism-check-history-section',

    components: {CharonSelect, ToggleButton,PopupSection},

    data() {
        return {
            submitDisabled: false,
            sectionTitle: 'Latest check',
            checkHistory: [],
            toggleShowHistoryTable: false,
            latestCheck: [],
            headers: [
                {text: 'Charon', align: 'start', value: 'assignment_name'},
                {text: 'Author', value: 'author'},
                {text: 'Created at', value: 'created_timestamp'},
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

    created() {
    },

    methods: {
        handleRunPlagiarismClicked() {
            Plagiarism.runPlagiarismCheck(this.charon.id, response => {
                if (response.status === 200) {
                    this.latestCheck = response.data.status
                    if (this.latestCheck.status === "Check started.") {
                        this.refreshLatestStatus(this.latestCheck.checkId, this.charon.id);
                    }
                }
                window.VueEvent.$emit(
                    'show-notification',
                    response.data.message,
                    'success',
                )
            })
        },
        showHistoryTable(bool) {
            this.toggleShowHistoryTable = bool;
            if (this.toggleShowHistoryTable === true) {
                this.sectionTitle = "History of checks";
                Plagiarism.getCheckHistory(this.course.id, response => {
                    response.forEach(timeObj => timeObj.created_timestamp = new Date(timeObj.created_timestamp).toLocaleString());
                    this.checkHistory = response;
                })
            } else {
                this.sectionTitle = "Latest check";
            }
        },
        refreshLatestStatus(latestCheckId, charonId) {
            this.submitDisabled = true;
            this.interval = setInterval(this.getLatestStatus, 5000, latestCheckId, charonId)
        },
        getLatestStatus(latestCheckId, charonId) {
            Plagiarism.getLatestCheckStatus(charonId, latestCheckId, response => {
                if (this.latestCheck.status !== response.status) {
                    this.latestCheck = response
                    clearInterval(this.interval)
                    this.submitDisabled = false
                }
            })
        }
    },

    beforeDestroy () {
        clearInterval(this.interval)
    },
}
</script>
