<template>

    <popup-section
            title="Students report"
            subtitle="Search in all students submissions in this course. "
    >
        <div>
            <vue-good-table
                :columns="columns"
                :rows="rows"
                :search-options="{
                    enabled: true
                    //trigger: 'enter'
                }"
                :sort-options="{
                    enabled: true,
                    initialSortBy: [
                        {field: 'gitTimestamp', type: 'desc'}
                        //{field: 'name', type: 'asc'}
                    ]
                }"
                :pagination-options="{
                    enabled: true,
                    mode: 'pages',
                    position: 'top',
                    perPage: 10,
                    perPageDropdown: [25, 50, 100],
                    dropdownAllowAll: false,
                }"/>
        </div>
    </popup-section>

</template>

<script>
    import { mapGetters } from 'vuex';
    import VueGoodTablePlugin from 'vue-good-table';
    import { Submission } from '../../../api/';
    import { PopupSection } from '../layouts/';


    export default {
        components: {PopupSection, VueGoodTablePlugin},

        data() {
            return {
                submissionsForReport: [],
                columns: [
                    {
                        label: 'First Name',
                        field: 'firstName',
                    },
                    {
                        label: 'Last Name',
                        field: 'lastName',
                    },
                    {
                        label: 'Exercise Name',
                        field: 'exerciseName',
                    },
                    {
                        label: 'Is Confirmed',
                        field: 'isConfirmed',
                        type: 'number',
                    },
                    {
                        label: 'Git push time',
                        field: 'gitTimestamp',
                        type: 'date',
                        dateInputFormat: 'YYYY-MM-DD HH:MM:SS',
                        dateOutputFormat: 'DD/MM/YYYY HH:MM:SS',
                    },
                ]
            };
        },

        computed: {
            ...mapGetters([
                'courseId'
            ]),

            rows() {
                return this.submissionsForReport;
            }
        },

        methods: {
            setSubmissionsForReport(submissionsForReport) {
                this.submissionsForReport = submissionsForReport.map(submission => {
                    return {submissionId: submission.id,
                            firstName: submission.firstname,
                            lastName: submission.lastname,
                            exerciseName: submission.name,
                            isConfirmed: submission.confirmed,
                            gitTimestamp: submission.git_timestamp}
                });
            },
        },

        created() {
            Submission.findAllSubmissionsForReport(this.courseId, this.setSubmissionsForReport)
        }
    };
</script>
