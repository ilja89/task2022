<template>
    <popup-section :title="title"
                   :subtitle="subtitle">

        <template slot="header-right">
            <v-btn v-if="this.logType" class="ma-2" tile outlined color="primary" @click="downloadLogs">Download logs</v-btn>
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
        </template>

        <v-card class="mx-auto" max-height="900" max-width="80vw" style="overflow: auto;" outlined raised>
            <v-list v-if="Array.isArray(logs)">
                <log-entry v-for="(log, index) in logs" v-bind:key="index" v-bind:log="log"></log-entry>
            </v-list>
            <pre v-else>{{ logs }}</pre>
        </v-card>

    </popup-section>
</template>

<script>
import {mapGetters} from 'vuex'
import {Charon, Submission} from '../../../api/index'
import {PopupSection} from '../layouts/index'
import LogEntry from '../partials/LogEntry.vue'

export default {
    name: 'log-section',

    components: {PopupSection, LogEntry},

    props: {
        title: {
            required: false,
            default: 'Charon logs'
        },

        subtitle: {
            required: false,
            default: 'Here are the recent errors charon has had'
        },

        logType: {
            required: false,
            default: false
        }
    },

    data() {
        return {
            logs: "Press get logs to get started",
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),
    },

    methods: {
        fetchLogs() {
            if (this.logType) {
                Charon.fetchLatestQueryLogs(this.courseId, logs => {
                    this.logs = logs
                })
            } else {
                Charon.fetchLatestLogs(this.courseId, logs => {
                    this.logs = logs
                })
            }
        },

        downloadLogs() {
            Charon.downloadQueryLogs(this.courseId, response => {
                console.log(response)
            })
        }
    },
}
</script>
