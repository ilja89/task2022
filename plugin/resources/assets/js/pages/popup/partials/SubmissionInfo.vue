<template>
    <div>


        <v-simple-table style="width: auto">
            <template v-slot:default>
                <thead>
                </thead>
                <tbody>

                <tr>
                    <td>Git time</td>
                    <td>{{ submission.git_timestamp }}</td>
                </tr>

                <tr v-if="submission.git_hash">
                    <td>Commit hash</td>
                    <td>
                        <a v-if="student" v-bind:href="getCommitLink">{{ submission.git_hash }}</a>
                        <a v-else href="#">{{ submission.git_hash }}</a>
                    </td>
                </tr>

                <tr v-if="submission.git_commit_message">
                    <td>Commit message</td>
                    <td>{{ submission.git_commit_message }}</td>
                </tr>

                <tr>
                    <td>Project folder</td>
                    <td>{{ charon ? charon.project_folder : '' }}</td>
                </tr>

                <tr v-if="charonCalculationFormula.length">
                    <td>Calculation formula</td>
                    <td>{{ charonCalculationFormula }}</td>
                </tr>

                <tr v-if="hasDeadlines">
                    <td>Deadlines</td>
                    <td>
                        <ul>
                            <li
                                    v-for="deadline in charon.deadlines"
                                    v-text="formatDeadline(deadline)"
                            />
                        </ul>
                    </td>
                </tr>

                <tr v-if="submission.grader">
                    <td>{{graderInfoTitle}}</td>
                    <td>{{ graderInfo }}</td>
                </tr>
                </tbody>
            </template>
        </v-simple-table>

    </div>
</template>

<script>
    import {mapState} from 'vuex'
    import SubmissionInfoBit from './SubmissionInfoBit'
    import {formatName, formatDeadline} from '../helpers/formatting'

    export default {

        components: {SubmissionInfoBit},

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

                if (this.student) {
                    gitUser = this.student.username.split("@")[0];
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
            formatDeadline,
        },
    }
</script>
