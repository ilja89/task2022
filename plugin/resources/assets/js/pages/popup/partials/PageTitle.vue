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
                    <div class="is-inline-block" v-if="totalPointsLabel">
                        <div class="chip badge badge--warning">{{totalPointsLabel}}</div>
                    </div>
                    <template v-if="groupsDirect.length > 0">

                        <v-col justify="center">
                            <v-expansion-panels popout>
                                <v-expansion-panel
                                        v-for="group in groupsDirect"
                                        :key="group.name"
                                >
                                    <v-expansion-panel-header>Item</v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <v-list>
                                            <template v-for="member in group.members">
                                                <v-list-item
                                                        :key="member.username"
                                                        @click=""
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
                        </v-col>
                    </template>
                </div>
            </div>
        </template>
    </h1>
</template>

<script>
    import {mapState, mapGetters} from "vuex";
    import VueTippy, {TippyComponent} from "vue-tippy";

    export default {
        components: {TippyComponent},
        data: () => ({}),
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
            }
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
