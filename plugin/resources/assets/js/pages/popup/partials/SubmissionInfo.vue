<template>
    <div>

        <submission-info-bit title="Git time">
            {{ removeDateSeconds(submission.git_timestamp.date) }}
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
                <li
                    v-for="deadline in charon.deadlines"
                    v-text="formatDeadline(deadline)"
                />
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
    import { mapState } from 'vuex'
    import SubmissionInfoBit from './SubmissionInfoBit'
    import { formatName, removeDateSeconds, formatDeadline } from '../helpers/formatting'

    export default {

        components: { SubmissionInfoBit },

        computed: {
            ...mapState([
                'charon',
                'submission',
            ]),

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
                return formatName(this.submission.grader)
            },
        },

        methods: {
            removeDateSeconds,

            formatDeadline,
        },
    }
</script>
