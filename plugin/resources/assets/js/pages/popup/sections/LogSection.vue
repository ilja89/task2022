<template>
    <popup-section title="Charon logs"
                   subtitle="Here are the recent errors charon has had">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
        </template>

        <v-card class="mx-auto" max-height="900" max-width="80vw" style="overflow: auto;" outlined raised>
            <v-list v-if="Array.isArray(logs)">
                <log-entry v-for="log in logs" v-bind:log="log"></log-entry>
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
                Charon.fetchLatestLogs(this.courseId, logs => {
                    this.logs = logs
                })
            },
        },
    }
</script>
