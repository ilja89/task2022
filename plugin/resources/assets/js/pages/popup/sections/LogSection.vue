<template>
    <popup-section :title="title"
                   :subtitle="subtitle">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
            <v-btn v-if="queryLogType" class="ma-2" tile outlined color="primary" @click="downloadLogs">Download logs
            </v-btn>
            <div v-if="queryLogType" style="width: 360px">
                <v-select
                    class="mx-auto"
                    v-model="enabledUsers"
                    :items="users"
                    item-text="username"
                    item-value="id"
                    multiple
                    chips
                    hint="Select users to enable query logging"
                    persistent-hint
                >
                    <template v-slot:selection="{ item, index }">
                        <v-chip v-if="index === 0">
                            <span>{{ item.username }}</span>
                        </v-chip>
                        <span
                            v-if="index === 1"
                            class="grey--text text-caption"
                        >
                            (+{{ enabledUsers.length - 1 }} others)
                        </span>
                    </template>
                </v-select>
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
import Vue from "vue";

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
            enabledUsers: []
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        allEnabledUsers() {
            return this.enabledUsers
        },
    },

    created() {
        if (this.queryLogType) {
            this.fetchUsers()
        }
    },

    methods: {
        enableLoggingCurrentUser(userId) {
            Log.enableLogging(this.courseId, userId, () => {
            })
        },

        disableLoggingCurrentUser(userId) {
            Log.disableLogging(this.courseId, userId, () => {
            })
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

        fetchUsers() {
            User.getAllEnrolled(this.courseId, enrolledUsers => {
                this.users = Object.keys(enrolledUsers).map(function (key) {
                    return enrolledUsers[key]
                });
                this.fetchEnabledUsers()
            })
        },

        fetchEnabledUsers() {
            Log.findUsersWithLoggingEnabled(this.courseId, enabledIds => {
                this.enabledUsers = this.users
                    .filter(user => {
                        return enabledIds.includes(parseInt(user.id))
                    })
                    .map(user => {
                        if (user.id) {
                            return user.id
                        }
                        return user
                    })

                this.$watch('allEnabledUsers', (newVal, oldVal) => {
                    if (newVal.length > oldVal.length) {
                        let addedUserId = newVal.filter(x => !oldVal.includes(x)).pop()
                        this.enableLoggingCurrentUser(parseInt(addedUserId))
                    } else {
                        let removedUserId = oldVal.filter(x => !newVal.includes(x)).pop()
                        this.disableLoggingCurrentUser(parseInt(removedUserId))
                    }
                })
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

            for (const log of logsArray) {
                innerLogsArray.push(log.join('\n'))
            }

            return innerLogsArray.join('\n\n')
        }
    },
}
</script>

<style>

</style>