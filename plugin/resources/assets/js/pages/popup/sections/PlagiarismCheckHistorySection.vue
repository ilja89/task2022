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
                                    {{latestCheck.charon}}
                                </td>
                                <td>
                                    {{latestCheck.author}}
                                </td>
                                <td>
                                    {{latestCheck.createdAt}}
                                </td>
                                <td>
                                    {{latestCheck.updatedAt}}
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
                  <template v-slot:expanded-item="{ headers, item: checkHistory  }">
                    <v-data-table
                        :items="[checkHistory]">
                    </v-data-table>
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
                {text: 'Created at', value: 'createdAt'},
                {text: 'Updated at', value: 'updatedAt'},
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
                    this.latestCheck.createdAt = new Date(this.latestCheck.createdAt).toLocaleString()
                    this.latestCheck.updatedAt = new Date(this.latestCheck.updatedAt).toLocaleString()
                    if (this.latestCheck.checkFinished === false) {
                        this.refreshLatestStatus(this.latestCheck.runId, this.charon.id);
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
                        timeObj.createdAt = new Date(timeObj.createdAt).toLocaleString()
                        timeObj.updatedAt = new Date(timeObj.updatedAt).toLocaleString()
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
                this.latestCheck.createdAt = new Date(this.latestCheck.createdAt).toLocaleString()
                this.latestCheck.updatedAt = new Date(this.latestCheck.updatedAt).toLocaleString()
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
