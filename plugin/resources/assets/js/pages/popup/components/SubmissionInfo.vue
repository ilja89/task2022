<template>
    <div>
        <submission-info-bit title="Git time">
            {{ formatDatetime(submission.git_timestamp.date) }}
        </submission-info-bit>

        <submission-info-bit
                v-if="submission.git_hash"
                title="Commit hash"
        >
            {{ submission.git_hash }}
        </submission-info-bit>

        <submission-info-bit
                v-if="submission.git_commit_message"
                title="Commit message"
        >
            {{ submission.git_commit_message }}
        </submission-info-bit>

        <submission-info-bit title="Project folder">
            {{ charon ? charon.project_folder : '' }}
        </submission-info-bit>

        <submission-info-bit
                v-if="charonCalculationFormula.length"
                title="Calculation formula"
        >
            {{ charonCalculationFormula }}
        </submission-info-bit>

        <submission-info-bit
                v-if="hasDeadlines"
                title="Deadlines"
        >
            <ul>
                <li v-for="deadline in charon.deadlines" v-text="formatDeadline(deadline)">
                </li>
            </ul>
        </submission-info-bit>

        <submission-info-bit
                v-if="submission.grader"
                :title="graderInfoTitle"
        >
            {{ graderInfo }}
        </submission-info-bit>
    </div>
</template>

<script>
    import SubmissionInfoBit from './SubmissionInfoBit.vue'

    export default {

        components: { SubmissionInfoBit },

        props: {
            charon: {
                required: true,
            },
            submission: {
                required: true,
                type: Object,
            },
        },

        computed: {
            charonCalculationFormula() {
                return this.charon !== null
                    ? this.charon.calculation_formula
                    : ''
            },

            hasDeadlines() {
                return this.charon && this.charon.deadlines.length !== 0
            },

            graderInfoTitle() {
                if (this.submission.confirmed) {
                    return 'Grader'
                } else {
                    return 'Previously graded by'
                }
            },

            graderInfo() {
                const grader = this.submission.grader
                if (! grader) return null
                let info;

                if (! grader.idnumber) {
                    info = `${grader.firstname} ${grader.lastname}`
                } else {
                    info = `${grader.firstname} ${grader.lastname} (${grader.idnumber})`
                }

                return info
            },
        },

        methods: {
            formatDatetime(date) {
                return date.replace(/:[0-9]{2}\.[0-9]+/, '')
            },

            formatDeadline(deadline) {
                const date = this.formatDatetime(deadline.deadline_time.date)
                const percentage = deadline.percentage
                const groupName = deadline.group
                    ? deadline.group.name
                    : 'All groups'

                return `${date} - ${percentage}% (${groupName})`
            },
        },
    }
</script>
