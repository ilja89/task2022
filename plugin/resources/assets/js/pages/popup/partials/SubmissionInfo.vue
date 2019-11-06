<template>
    <div>

        <submission-info-bit title="Git time">
            {{ removeDateSeconds(submission.git_timestamp.date) }}
        </submission-info-bit>

        <submission-info-bit
            v-if="submission.git_hash"
            title="Commit hash"
        >
            <a v-if="student" v-bind:href="getCommitLink">{{ submission.git_hash }}</a>
            <a v-else href="#">{{ submission.git_hash }}</a>
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
                'student',
                'submission',
                'course',
            ]),

            charonCalculationFormula() {
                return this.charon !== null
                    ? this.charon.calculation_formula
                    : ''
            },

            hasDeadlines() {
                return this.charon && this.charon.deadlines.length !== 0
            },
            getCommitLink() {
                var gitlabUrl = "https://gitlab.cs.ttu.ee/";
                var gitUser;
                var courseShortname;

                if(this.student) {
                    gitUser = this.student.idnumber.split("@")[0];
                } else {
                    gitUser = "" // this should never happen
                }
                courseShortname = window.course_shortname;

                return gitlabUrl + gitUser + "/" + courseShortname + "/commit/" + this.submission.git_hash
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
