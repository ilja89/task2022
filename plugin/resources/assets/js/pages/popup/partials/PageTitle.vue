<template>
    <h1
            class="title is-3 page-title bottom-border-separator"
            :class="{ 'title--with-right': $slots.default }"
    >
        <template v-if="hasRight">
            <div>{{ currentTitle }}</div>
            <div>
                <slot></slot>
            </div>
        </template>
        <template v-else>
            <div>
                {{ currentTitle }}
                <div v-if="this.student" style="vertical-align: text-bottom;" class="is-inline-block student-groups">

                    <v-chip v-if="totalPointsLabel"
                            class="ma-2"
                            color="primary"
                    >
                        {{totalPointsLabel}}
                    </v-chip>

                </div>

                <v-col justify="center">

                    <v-row>
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
                                                    :key="member.username"
                                                    @click="doCopy(member.username)"
                                            >
                                                <v-list-item-content>
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
                    </v-row>

                </v-col>


            </div>

            <v-snackbar
                    top
                    right
                    absolute
                    shaped
                    v-model="this.notification.show"
                    :timeout="this.notification.timeout"
            >
                {{ notification.text }}

                <template v-slot:action="{ attrs }">
                    <v-btn
                            color="blue"
                            text
                            v-bind="attrs"
                            @click="this.notification.show = false"
                    >
                        Close
                    </v-btn>
                </template>
            </v-snackbar>

        </template>
    </h1>
</template>

<script>
    import {mapState, mapGetters} from "vuex";
    import VueTippy, {TippyComponent} from "vue-tippy";

    export default {
        components: {TippyComponent},
        data: () => ({
        }),
        props: {
            title: {
                required: false,
                default: ""
            }
        },

        computed: {
            ...mapState(["student"]),
            hasRight() {
                return !!this.$slots.default;
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
                this.showNotification("Copied to clipboard!")
                this.$copyText(name)
            },

            showNotification(message, type = 'success', timeout = 1000) {
                this.notification.text = message
                this.notification.show = true
                this.notification.type = type
                this.notification.timeout = timeout
            },
        }
    };
</script>

<style lang="scss" scoped>
    .title--with-right {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
