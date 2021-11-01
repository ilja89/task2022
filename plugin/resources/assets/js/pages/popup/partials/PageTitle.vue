<template>
    <v-card class="mb-16 pl-4">
        <v-row justify="start">
            <v-card-title>{{ currentTitle }}</v-card-title>
            <template v-if="hasRight">
                <div>
                    <slot></slot>
                </div>
            </template>
            <template v-else>
                <v-row >
                    <v-col cols="12" sm="4" md="4">
                        <div v-if="this.student" style="vertical-align: text-bottom;"
                             class="is-inline-block student-groups">

                            <v-chip v-if="totalPointsLabel" class="ma-2" color="primary">
                                {{totalPointsLabel}}
                            </v-chip>
                        </div>
                    </v-col>

                    <v-col cols="12" sm="4" md="4">
                        <v-expansion-panels popout>
                            <v-expansion-panel
                                    v-for="group in groupsDirect"
                                    :key="group.name"
                            >
                                <v-expansion-panel-header>{{group.name}}</v-expansion-panel-header>
                                <v-expansion-panel-content>
                                    <v-list>
                                        <template v-for="member in group.members">
                                            <v-list-item
                                                    @click="doCopy(member.username)"
                                            >
                                                <v-list-item-content
                                                    :key="member.username"
                                                >
                                                    <v-list-item-title>{{member.firstname}} {{member.lastname}}
                                                        ({{member.username}})
                                                    </v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                        </template>
                                    </v-list>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-expansion-panels>
                    </v-col>

                </v-row>
            </template>
            <v-card-title v-if="this.editCharonSettings">
                <v-btn class="ma-2" small tile outlined color="primary" @click="editClicked()">Edit
                </v-btn>
            </v-card-title>
        </v-row>

    </v-card>


</template>

<script>
    import {mapActions, mapState} from "vuex";
    import {TippyComponent} from "vue-tippy";
    import {Charon} from "../../../api/index";

    export default {
        components: {TippyComponent, Charon},
        data() {
            return {
                charon: {},
            }
        },
        props: {
            title: {
                required: false,
                default: ""
            },
            editCharonSettings: {
                required: false,
                default: false
            }
        },

        computed: {
            ...mapState(["student"]),
            hasRight() {
                return !!this.$slots.default;
            },

            routeCharonId() {
                return parseInt(this.$route.params.charon_id)
            },

            currentTitle() {
                if (this.student !== null) {
                    return `${this.student.firstname} ${this.student.lastname}`;
                } else {
                    return this.title;
                }
            },
            groupsDirect() {
                if (this.student !== null) {
                    return this.student.groups;
                } else {
                    return [];
                }
            },
            totalPointsLabel() {
                if (this.student !== null) {
                    return `Total points: ${this.student.totalPoints}`
                } else {
                    return null;
                }
            }
        },

        methods: {
            createBadgeName(groupId) {
                return "group_badge_" + groupId;
            },

            doCopy: function (name) {
                this.showNotification("Copied to clipboard!", 'success', 1000)
                this.$copyText(name)
            },

            showNotification(message, type, timeout = 5000) {
                VueEvent.$emit('show-notification', message, type, timeout)
            },

            editClicked() {
                Charon.getById(this.routeCharonId, response => {
                    this.charon = response
                    window.location = `popup#/charonSettings/${this.charon.id}`;
                })
            }
        }
    };
</script>
