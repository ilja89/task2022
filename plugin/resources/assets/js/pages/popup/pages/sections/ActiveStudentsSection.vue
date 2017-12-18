<template>
    <popup-section
            title="Active students"
            subtitle="Choose a time period to see active users"
    >
        <template slot="header-right">
            <div class="select  is-medium">
                <select
                        name="period"
                        v-model="period"
                        @change="onPeriodChanged"
                >
                    <option
                            v-for="selectPeriod in periods"
                            :value="selectPeriod.value"
                    >
                        {{ selectPeriod.label }}
                    </option>
                </select>
            </div>
        </template>

        <div class="card  has-padding">
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
        </div>
    </popup-section>
</template>

<script>
    import { mapGetters } from 'vuex'
    import { PopupSection } from '../../layouts'
    import { User } from '../../../../models'
    import { formatName } from '../../helpers/formatting'

    export default {
        name: "active-students-section",

        components: { PopupSection },

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
                const nrOfChunks = 3

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
    }
</script>

<style lang="scss" scoped>

    .active-students__list {
        list-style: disc;
        padding-left: 10px;
    }

</style>