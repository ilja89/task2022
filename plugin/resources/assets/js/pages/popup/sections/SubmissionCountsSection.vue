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
                    {text: 'Average grade', value: 'avg_grade'}
                ],
            }
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),
        },

        mounted() {
            this.fetchSubmissionCounts()
            VueEvent.$on('refresh-page', this.fetchSubmissionCounts);
        },

        activated() {
            VueEvent.$on('refresh-page', this.fetchSubmissionCounts);
        },

        /**
         * Remove global event listeners for more efficient refreshes on other
         * pages.
         */
        deactivated() {
            VueEvent.$off('refresh-page', this.fetchSubmissionCounts)
        },

        methods: {

            fetchSubmissionCounts() {
                Submission.findSubmissionCounts(this.courseId, counts => {
                    this.submission_counts = counts.map(item => {
                        const container = {};

                        container['project_folder'] = item.project_folder;
                        container['diff_users'] = item.diff_users;
                        container['tot_subs'] = item.tot_subs;
                        container['subs_per_user'] = parseFloat(item.subs_per_user);
                        container['avg_grade'] = parseFloat(item.avg_grade);

                        return container;
                    });
                })
            },
        },
    }
</script>
