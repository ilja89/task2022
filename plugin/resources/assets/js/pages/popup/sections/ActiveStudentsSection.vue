<template>
    <popup-section
            title="Active students"
            subtitle="Choose a time period to see active users"
    >
        <template slot="header-right">
            <popup-select
                    name="period"
                    :options="periods"
                    placeholder-key="label"
                    size="medium"
                    v-model="period"
            />
        </template>

        <v-card
                class="mx-auto"
                outlined
                hover
                light
                raised
                shaped
        >

            <div class="columns">
                <div
                        v-for="studentChunk in studentsChunks"
                        class="column"
                >
                    <ul class="active-students__list">
                        <li v-for="student in studentChunk">
                            <router-link :to="'/grading/' + student.id">
                                {{ formatName(student) }}
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>

        </v-card>

        <h3 v-if="!students.length" class="title  is-3">
            No active students!
        </h3>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {PopupSection} from '../layouts'
    import {User} from '../../../api'
    import {formatName} from '../helpers/formatting'
    import {PopupSelect} from '../partials'

    export default {
        name: "active-students-section",

        components: {PopupSection, PopupSelect},

        data() {
            return {
                students: [],
                period: 'day',
                periods: [
                    {
                        value: 'day',
                        label: '24h',
                    },
                    {
                        value: 'week',
                        label: 'Week',
                    },
                    {
                        value: 'month',
                        label: 'Month',
                    },
                ],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            studentsChunks() {
                const nrOfChunks = 4

                let chunks = []
                for (let i = 0; i < nrOfChunks; i++) {
                    chunks.push([])
                }
                let chunkIndex = 0

                this.students.forEach(student => {
                    chunks[chunkIndex].push(student)
                    chunkIndex++

                    if (chunkIndex === nrOfChunks) {
                        chunkIndex = 0
                    }
                })

                return chunks
            },
        },

        watch: {
            period() {
                this.onPeriodChanged()
            },
        },

        methods: {
            formatName,

            fetchActiveStudents() {
                User.findActiveUsers(this.courseId, this.period, users => {
                    this.students = users
                })
            },

            onPeriodChanged() {
                this.fetchActiveStudents()
            },
        },

        mounted() {
            this.fetchActiveStudents()
            VueEvent.$on('refresh-page', this.fetchActiveStudents);
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off('refresh-page', this.fetchActiveStudents)
        },
    }
</script>

<style lang="scss" scoped>

    .active-students__list {
        text-align: center;
    }

</style>