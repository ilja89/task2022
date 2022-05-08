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
                <v-simple-table
                    class="center-table"
                >
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
                                    {{latestCheck.charon}}
                                </td>
                                <td>
                                    {{latestCheck.author}}
                                </td>
                                <td>
                                    {{latestCheck.created_timestamp}}
                                </td>
                                <td>
                                    {{latestCheck.updated_timestamp}}
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
                item-key="run_id"
                :items-per-page="5"
                :single-expand="true"
                show-expand
                class="elevation-1 center-table">
                    <template v-slot:item="{ item, expand, isExpanded }">
                        <tr>
                            <td
                                class="d-block d-sm-table-cell"
                                v-for="header in headers"
                                style="width: 19%"
                            >
                              {{item[header.value]}}
                            </td>
                            <td style="width: 5%">
                                <v-btn icon @click="expand(!isExpanded)">
                                  <v-icon>{{ isExpanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </template>
                        <template v-slot:expanded-item="{ headers, item, isExpanded }">
                            <tr v-for="row in item['history']">
                              <td></td>
                              <td></td>
                              <td>{{ new Date(row.created_timestamp).toLocaleString('et-EE') }}</td>
                              <td></td>
                              <td>{{ row.status }}</td>
                            </tr>
                        </template>
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

    components: {CharonSelect, ToggleButton, PopupSection},

    data() {
        return {
            submitDisabled: false,
            sectionTitle: 'Latest check',
            checkHistory: [],
            toggleShowHistoryTable: false,
            latestCheck: [],
            headers: [
                {text: 'Charon', align: 'start', value: 'charon'},
                {text: 'Author', value: 'author'},
                {text: 'Created at', value: 'created_timestamp'},
                {text: 'Updated at', value: 'updated_timestamp'},
                {text: 'Status', value: 'status'},
                {text: '', value: 'data-table-expand'},
            ],
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
                    this.latestCheck.created_timestamp = new Date(this.latestCheck.created_timestamp).toLocaleString('et-EE')
                    this.latestCheck.updated_timestamp = new Date(this.latestCheck.updated_timestamp).toLocaleString('et-EE')
                    if (this.latestCheck.check_finished === false) {
                        this.refreshLatestStatus(this.latestCheck.run_id, this.charon.id);
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
                    response.forEach(timeObj => {
                        timeObj.created_timestamp = new Date(timeObj.created_timestamp).toLocaleString('et-EE')
                        timeObj.updated_timestamp = new Date(timeObj.updated_timestamp).toLocaleString('et-EE')
                    });
                    this.checkHistory = response;
                })
            } else {
                this.sectionTitle = "Latest check";
            }
        },
        refreshLatestStatus(latestRunId, charonId) {
            this.submitDisabled = true;
            this.interval = setInterval(this.getLatestStatus, 5000, latestRunId, charonId)
        },
        getLatestStatus(latestRunId, charonId) {
            Plagiarism.getLatestCheckStatus(charonId, latestRunId, response => {
                this.latestCheck = response
                this.latestCheck.created_timestamp = new Date(this.latestCheck.created_timestamp).toLocaleString()
                this.latestCheck.updated_timestamp = new Date(this.latestCheck.updated_timestamp).toLocaleString()
                if (this.latestCheck.check_finished === true) {
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

<style>
.center-table table td{
    vertical-align: middle;
}
.center-table table th{
    vertical-align: middle;
}
</style>
