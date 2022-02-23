<template>
    <popup-section :title="title"
                   :subtitle="subtitle">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
            <v-btn v-if="queryLogType" class="ma-2" tile outlined color="primary" @click="downloadLogs">Download logs</v-btn>
            <div v-if="queryLogType" class="ma-2">
                <v-select
                    class="mx-auto"
                    dense
                    single-line
                    item-text="username"
                    item-value="id"
                    :items="users"
                    @change="this.updateUser"
                ></v-select>
                <v-btn
                    elevation="2"
                    x-small
                    @click="enableLoggingCurrentUser"
                >Enable logging</v-btn>
                <v-btn
                    elevation="2"
                    x-small
                    @click="disableLoggingCurrentUser"
                >Disable logging</v-btn>
            </div>
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
import {Charon} from '../../../api/index'
import {PopupSection} from '../layouts/index'
import LogEntry from '../partials/LogEntry'
import User from "../../../api/User";
import Log from "../../../api/Log";

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

        queryLogType: {
            required: false,
            default: false
        }
    },

    data() {
        return {
            logs: "Press get logs to get started",
            users: [],
            currentUser: false,
            currentUserLoggingEnabled: false,
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),
    },

    created() {
        this.fetchEnrolledUsers()
    },

    methods: {
        enableLoggingCurrentUser() {
            if (this.currentUser && !this.currentUserLoggingEnabled) {
                Log.enableLogging(this.courseId, this.currentUser, {})
                this.currentUserLoggingEnabled = true
            }
        },

        disableLoggingCurrentUser() {
            if (this.currentUser && this.currentUserLoggingEnabled) {
                Log.disableLogging(this.courseId, this.currentUser, {})
                this.currentUserLoggingEnabled = false
            }
        },

        fetchLogs() {
            if (this.queryLogType) {
                Charon.fetchLatestQueryLogs(this.courseId, logs => {
                    this.logs = logs
                })
            } else {
                Charon.fetchLatestLogs(this.courseId, logs => {
                    this.logs = logs
                })
            }
        },

        fetchEnrolledUsers() {
            User.getAllEnrolled(this.courseId, enrolledUsers => {
                this.users = Object.keys(enrolledUsers).map(function (key) {
                    return enrolledUsers[key]
                });
            })
        },

        updateUser(user) {
            this.currentUser = user
            Log.userHasLoggingEnabled(this.courseId, user, loggingEnabled => {
                this.currentUserLoggingEnabled = loggingEnabled
            })
        },

        downloadLogs() {
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(this.formatQueryLogs(this.logs)))
            element.setAttribute('download', 'queryLogs.txt')

            element.style.display = 'none'
            document.body.appendChild(element)

            element.click()

            document.body.removeChild(element)
        },

        formatQueryLogs(logsArray) {
            const innerLogsArray = [];

            // Loop through all logs. Logs are arrays consisting of inner logs. Join inner logs to a string with new line
            // separator. After joining inner logs, add them to array and do the same for the rest of inner logs.
            // Return all logs at the end joined by 2 new line separators for clean formatting.
            for (const log of logsArray) {
                innerLogsArray.push(log.join('\n'))
            }

            return innerLogsArray.join('\n\n')
        }
    },
}
</script>
