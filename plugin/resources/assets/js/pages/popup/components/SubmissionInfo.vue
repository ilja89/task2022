<template>
    <div>
        <submission-info-bit title="Git time">
            {{ submission.git_timestamp.date | datetime }}
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
            {{ charon.project_folder }}
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
                <li v-for="deadline in charon.deadlines">{{ deadline.deadline_time.date | datetime }} - {{ deadline.percentage }}%</li>
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
                type: Object,
            },
            submission: {
                required: true,
                type: Object,
            },
        },

        filters: {
            datetime(date) {
                return date.replace(/:[0-9]{2}\.[0-9]+/, '')
            },
        },

        computed: {
            charonCalculationFormula() {
                return this.charon !== null
                    ? this.charon.calculation_formula
                    : ''
            },

            hasDeadlines() {
                return this.charon.deadlines.length !== 0
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
    }
</script>
