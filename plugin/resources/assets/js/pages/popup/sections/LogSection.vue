<template>
    <popup-section title="Charon logs"
                   subtitle="Here are the recent errors charon has had">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
        </template>

        <v-card class="mx-auto" max-height="900" max-width="80vw" outlined raised>
            <pre style="max-height: 900px;overflow: auto">{{logs}}</pre>
        </v-card>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {Charon, Submission} from '../../../api/index'
    import {PopupSection} from '../layouts/index'

    export default {
        name: 'log-section',

        components: {PopupSection},

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
