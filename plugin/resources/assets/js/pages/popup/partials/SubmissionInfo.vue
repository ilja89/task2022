<template>
    <div>


        <v-simple-table style="width: auto">
            <template v-slot:default>
                <thead>
                </thead>
                <tbody>

                <tr>
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Git time:</td>
                        <td>{{ submission.git_timestamp }}</td>
                    </v-container>
                </tr>

                <tr v-if="submission.git_hash">
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Commit hash:</td>
                        <td>
                            <a v-if="student" v-bind:href="getCommitLink">{{ submission.git_hash }}</a>
                            <a v-else href="#">{{ submission.git_hash }}</a>
                        </td>
                    </v-container>
                </tr>

                <tr v-if="submission.git_commit_message">
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Commit message:</td>
                        <td>{{ submission.git_commit_message }}</td>
                    </v-container>
                </tr>

                <tr>
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Project folder:   </td>
                        <td>{{ charon ? charon.project_folder : '' }}</td>
                    </v-container>
                </tr>

                <tr v-if="charonCalculationFormula.length">
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Calculation formula:</td>
                        <td>{{ charonCalculationFormula }}</td>sc
                    </v-container>
                </tr>

                <tr>
                  <v-container
                      class="spacing-playground pa-3"
                      fluid
                  >
                    <td class=" pr-3">Hello sharon:</td>
                    <td>{{ charonCalculationFormula }}</td>
                  </v-container>
                </tr>

                <tr v-if="hasDeadlines">
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">Deadlines:</td>
                        <td>
                            <ul>
                                <li
                                        v-for="deadline in charon.deadlines"
                                        v-text="formatDeadline(deadline)"
                                />
                            </ul>
                        </td>
                    </v-container>
                </tr>

                <tr v-if="submission.grader">
                    <v-container
                            class="spacing-playground pa-3"
                            fluid
                    >
                        <td class=" pr-3">{{graderInfoTitle}}</td>
                        <td>{{ graderInfo }}</td>
                    </v-container>
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
                    return 'Grader:'
                } else {
                    return 'Previously graded by:'
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
