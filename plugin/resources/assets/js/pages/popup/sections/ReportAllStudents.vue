<template>

    <popup-section
            title="Students report"
            subtitle="Search in all students submissions in this course. "
    >
        <div>
            <vue-good-table ref="myTable"
                :columns="columns"
                :rows="rows"
                :search-options="{
                    enabled: false
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
                }"
                max-height="750px"
                :fixed-header="true"
                :line-numbers="false"
                @on-column-filter="onColumnFilter"
            >
                <div slot="table-actions">
                    <vue-json-to-csv
                        :json-data="jsonData"
                        :csv-title="'ReportExportToCsvTable'"
                        :labels="{ firstName: { title: 'First name' },
                                   lastName: { title: 'Last Name' },
                                   exerciseName: { title: 'Exercise Name' },
                                   submissionResult: { title: 'Submission Result' },
                                   submissionTotal: { title: 'Submission Total' },
                                   isConfirmed: { title: 'Is Confirmed' },
                                   gitTimestamp: { title: 'Git Push Time' } }"
                        :separator="';'">
                        <button class="button is-primary">
                            Export CSV Table
                        </button>
                    </vue-json-to-csv>
                    <vue-json-to-csv
                        :json-data="jsonData"
                        :csv-title="'ReportExportToCsvRaw'">
                        <button class="button is-primary">
                            Export CSV Raw
                        </button>
                    </vue-json-to-csv>
                </div>
            </vue-good-table>
        </div>

    </popup-section>

</template>

<script>
    import {mapGetters} from 'vuex';
    import VueGoodTablePlugin from 'vue-good-table';
    import VueJsonToCsv from 'vue-json-to-csv';
    import {Submission} from '../../../api/';
    import {PopupSection} from '../layouts/';

    export default {
        components: {PopupSection, VueGoodTablePlugin, VueJsonToCsv},

        data() {
            return {
                submissionsForReport: [],
                columns: [
                    {
                        label: 'First Name',
                        field: 'firstName',
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Filter First Name', // placeholder for filter input
                            //filterValue: '', // initial populated value for this filter
                            filterDropdownItems: this.submissionsForReport, // dropdown (with selected values) instead of text input
                            //filterFn: this.columnFilterFn, //custom filter function that
                            //trigger: 'enter', //only trigger on enter not on keyup
                        },
                    },
                    {
                        label: 'Last Name',
                        field: 'lastName',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter Last Name',
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                    {
                        label: 'Exercise Name',
                        field: 'exerciseName',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter Exercises',
                            filterDropdownItems: this.submissionsForReport,
                        },
                        width: '155px',
                    },
                    {
                        label: 'Submission Result',
                        field: 'submissionResult',
                        formatFn: this.formatSubmissionResult,
                        width: '290px'
                    },
                    {
                        label: 'Submission Total',
                        field: 'submissionTotal',
                        width: '170px',
                    },
                    {
                        label: 'Is Confirmed',
                        field: 'isConfirmed',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter (un)confirmed',
                            filterValue: '',
                            filterDropdownItems: this.submissionsForReport,
                        },
                        width: '180px',
                    },
                    {
                        label: 'Git push time',
                        field: 'gitTimestamp',
                        type: 'datetime',
                        width: '185px'
                    },
                ],
                search: {}
            };
        },

        computed: {
            ...mapGetters([
                'courseId'
            ]),

            rows() {
                return this.submissionsForReport;
            },

            jsonData() {
                this.search.changed = true;
                return this.$refs.myTable ? this.$refs.myTable.filteredRows[0].children : [];
            }
        },

        methods: {
            setSubmissionsForReport(submissionsForReport) {
                this.submissionsForReport = submissionsForReport.map(submission => {
                    return {
                        submissionId: submission.id,
                        firstName: submission.firstname,
                        lastName: submission.lastname,
                        exerciseName: submission.name,
                        submissionResult: submission.submission_result,
                        submissionTotal: submission.finalgrade,
                        isConfirmed: submission.confirmed,
                        gitTimestamp: submission.git_timestamp
                    };
                });
            },

            formatSubmissionResult(value) {
                return '| ' + value + ' |';
            },

            onColumnFilter(searchValue) {
                this.search = searchValue;
            },
        },

        created() {
            Submission.findAllSubmissionsForReport(this.courseId, this.setSubmissionsForReport);
        }
    };
</script>
