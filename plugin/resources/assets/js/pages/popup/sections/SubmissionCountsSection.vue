<template>
    <popup-section title="Submission counts"
                   subtitle="Submission counts and averages for Charons.">

        <v-card-title v-if="submission_counts.length">
            Charons
            <v-spacer></v-spacer>
            <v-text-field
                    v-if="submission_counts.length"
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details>
            </v-text-field>
        </v-card-title>
        <v-card-title v-else>
            No Charons for this course!
        </v-card-title>

        <v-data-table
                v-if="submission_counts.length"
                :headers="submission_count_headers"
                :items="submission_counts"
                :search="search">
            <template v-slot:no-results>
                <v-alert :value="true" color="primary" icon="warning">
                    Your search for "{{ search }}" found no results.
                </v-alert>
            </template>
        </v-data-table>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex'
    import {Submission} from '../../../api/index'
    import {PopupSection} from '../layouts/index'

    export default {
        name: 'submission-counts-section',

        components: {PopupSection},

        data() {
            return {
                search: '',
                submission_counts: [],
                submission_count_headers: [
                    {text: 'Charon', value: 'project_folder', align: 'start'},
                    {text: 'Different users', value: 'diff_users'},
                    {text: 'Total submissions', value: 'tot_subs'},
                    {text: 'Submissions per user', value: 'subs_per_user'},
                    {text: 'Average defended grade', value: 'avg_defended_grade'},
                    {text: 'Average test grade', value: 'avg_raw_grade'},
                    {text: 'Undefended amount', value: 'undefended'}
                ],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),
        },

        created() {
            this.fetchSubmissionCounts()
        },

        methods: {

            fetchSubmissionCounts() {
                Submission.findSubmissionCounts(this.courseId, counts => {
                    this.submission_counts = counts.map(item => {
                        const container = {};

                        container['project_folder'] = item.project_folder;
                        container['diff_users'] = item.diff_users;
                        container['tot_subs'] = item.tot_subs;
                        container['subs_per_user'] = parseFloat(item.subs_per_user).toPrecision(2);
                        container['avg_defended_grade'] = parseFloat(item.avg_defended_grade).toPrecision(2);
                        container['avg_raw_grade'] = parseFloat(item.avg_raw_grade).toPrecision(2);
                        container['undefended'] = parseInt(item.successful_tests_amount) - parseInt(item.defended_amount);

                        return container;
                    });
                })
            },
        },
    }
</script>
