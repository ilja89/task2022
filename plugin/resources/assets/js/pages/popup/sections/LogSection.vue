<template>
    <popup-section :title="title"
                   :subtitle="subtitle">

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="fetchLogs">Get Logs</v-btn>
            <v-btn v-if="queryLogType" class="ma-2" tile outlined color="primary" @click="downloadLogs">Download logs
            </v-btn>
            <div v-if="queryLogType" class="ma-2">
                <v-col cols="auto">
                    <v-select
                        class="mx-auto"
                        dense
                        single-line
                        item-text="username"
                        item-value="id"
                        :items="users"
                        label="Enrolled users"
                        @change="updateUser"
                    ></v-select>
                </v-col>
                <v-btn
                    elevation="2"
                    x-small
                    :disabled="!currentUser || currentUserHasLogging"
                    @click="enableLoggingCurrentUser"
                >Enable logging
                </v-btn>
                <v-btn
                    elevation="2"
                    x-small
                    :disabled="!currentUser || !currentUserHasLogging"
                    @click="disableLoggingCurrentUser"
                >Disable logging
                </v-btn>
            </div>
            <v-col v-if="queryLogType" cols="auto">
                <v-select
                    class="mx-auto"
                    dense
                    single-line
                    item-text="username"
                    item-value="id"
                    label="Enabled users"
                    :items="usersWithLogging"
                ></v-select>
            </v-col>

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
            currentUser: null,
        }
    },

    computed: {
        ...mapGetters([
            'courseId',
        ]),

        currentUserHasLogging() {
            return this.currentUser ? this.currentUser.logging : null
        },

        usersWithLogging() {
            return this.users.filter(user => {
                return user.logging
            })
        }
    },

    created() {
        if (this.queryLogType) {
            this.fetchEnrolledUsers()
        }
    },

    methods: {
        enableLoggingCurrentUser() {
            if (this.currentUser && !this.currentUserHasLogging) {
                Log.enableLogging(this.courseId, this.currentUser.id, () => {
                    this.currentUser.logging = true
                })
            }
        },

        disableLoggingCurrentUser() {
            if (this.currentUser && this.currentUserHasLogging) {
                Log.disableLogging(this.courseId, this.currentUser.id, () => {
                    this.currentUser.logging = false
                })
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
                this.updateUsersWithLoggingEnabled()
            })
        },

        updateUsersWithLoggingEnabled() {
            Log.findUsersWithLoggingEnabled(this.courseId, enabledIds => {
                this.users = this.users.map(user => {
                    Vue.set(user, 'logging', !!enabledIds.includes(parseInt(user.id)))
                    return user
                })
            })
        },

        updateUser(newUser) {
            this.users.forEach(user => {
                if (user.id === newUser) {
                    this.currentUser = user
                }
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
.v-select__selections input { width: 50px }
</style>