<template>

    <popup-section
            title="Students report"
            subtitle="Search in all students submissions in this course.
                     <br/>
                     <br/> For activating preset period or period selected from calendar click on filter and press enter.">

        <v-card class="mx-auto" outlined light raised>
            <v-container class="spacing-playground pa-3" fluid>
                <vue-good-table ref="reportTable"
                                :columns="columns"
                                :rows="rows"
                                :mode="remote"
                                :totalRows="totalRecords"
                                @on-page-change="onPageChange"
                                @on-sort-change="onSortChange"
                                @on-column-filter="onColumnFilter"
                                @on-per-page-change="onPerPageChange"
                                :isLoading.sync="isLoading"
                                :fixed-header="false"
                                :line-numbers="false"
                                :search-options="{
                    enabled: false
                    //trigger: 'enter'
                }"
                                :sort-options="{
                    enabled: true,
                    initialSortBy:
                        {field: 'gitTimestampForEndDate', type: 'desc'}
                        //{field: 'name', type: 'asc'}
                }"
                                :pagination-options="{
                    enabled: true,
                    mode: 'pages',
                    position: 'top',
                    perPageDropdown: [10, 25, 50, 100, 1000, 10000, 100000],
                    dropdownAllowAll: false,
                }"
                                max-height="750px"
                >
                    <div slot="table-actions">
                        <v-btn class="ma-2" tile outlined color="primary" @click="resetFilters">Reset Filters</v-btn>

                        <date-range-picker
                                :startDate="startDate"
                                :endDate="endDate"
                                :locale-data="locale"
                                :opens="opens"
                                :ranges="ranges"
                                @update="updatePeriodDates"
                        >
                            <button class="button is-primary" slot="input" slot-scope="picker">
                                Select Period
                            </button>
                        </date-range-picker>

                        <vue-json-to-csv
                                :json-data="jsonData"
                                :csv-title="'ReportExportToCsvTable'"
                                :labels="{ firstName: { title: 'First name' },
                                   lastName: { title: 'Last Name' },
                                   exerciseName: { title: 'Exercise Name' },
                                   submissionResult: { title: 'Submission Result' },
                                   submissionTestsSum: { title: 'Submission Tests Sum' },
                                   submissionTotal: { title: 'Submission Total' },
                                   isConfirmed: { title: 'Is Confirmed' },
                                   gitTimestampForStartDate: { title: 'Git Commit' }
                        }"
                                :separator="';'">
                            <v-btn class="ma-2" tile outlined color="primary">Export CSV Table</v-btn>
                        </vue-json-to-csv>

                        <vue-json-to-csv
                                :json-data="jsonData"
                                :csv-title="'ReportExportToCsvRaw'">
                            <v-btn class="ma-2" tile outlined color="primary"> Export CSV Raw</v-btn>
                        </vue-json-to-csv>
                    </div>
                </vue-good-table>
            </v-container>
        </v-card>

    </popup-section>

</template>

<script>
    import {mapGetters} from 'vuex';
    import {Submission} from '../../../api/';
    import {PopupSection} from '../layouts/';
    import VueGoodTablePlugin from 'vue-good-table';
    import VueJsonToCsv from 'vue-json-to-csv';
    import DateRangePicker from 'vue2-daterange-picker'
    import 'vue2-daterange-picker/dist/lib/vue-daterange-picker.min.css'
    import moment from "moment";

    export default {
        components: {PopupSection, VueGoodTablePlugin, DateRangePicker, VueJsonToCsv,},

        data() {
            return {
                remote: "remote",
                isLoading: false,
                submissionsForReport: [],
                rows: [],
                totalRecords: 0,
                serverParams: {
                    columnFilters: {},
                    sort: {
                        field: 'gitTimestampForEndDate',
                        type: 'desc',
                    },
                    page: 1,
                    perPage: 10
                },
                search: {}, // Object is needed for filtered data export.
                columns: [
                    {
                        label: 'First Name',
                        field: 'firstName',
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Type First Name', // placeholder for filter input
                            filterValue: '', // initial populated value for this filter
                            filterDropdownItems: this.rows, // dropdown (with selected values) instead of text input
                            //filterFn: this.columnFilterFn, //custom filter function that
                            trigger: 'enter', //only trigger on enter not on keyup
                        },
                    },
                    {
                        label: 'Last Name',
                        field: 'lastName',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Type Last Name',
                            filterValue: '',
                            filterDropdownItems: this.rows,
                            trigger: 'enter',
                        },
                    },
                    {
                        label: 'Exercise Name',
                        field: 'exerciseName',
                        width: '155px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Type Exercise',
                            filterValue: '',
                            filterDropdownItems: this.rows,
                            trigger: 'enter',
                        },
                    },
                    {
                        label: 'Submission Result',
                        field: 'submissionResult',
                        width: '190px',
                        formatFn: this.formatSubmissionResult
                    },
                    {
                        label: 'Tests Sum',
                        field: 'submissionTestsSum',
                        type: 'number',
                        width: '120px',
                    },
                    {
                        label: 'Total',
                        field: 'submissionTotal',
                        type: 'number',
                        width: '75px',
                    },
                    {
                        label: 'Is Confirmed',
                        field: 'isConfirmed',
                        type: 'number',
                        width: '140px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Type 0 or 1',
                            filterValue: '',
                            filterDropdownItems: this.rows,
                            //trigger: 'enter',
                        },
                    },
                    {
                        label: 'Period Start',
                        field: 'gitTimestampForStartDate',
                        type: 'datetime',
                        width: '195px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Type Commit Time',
                            filterValue: moment().subtract(1, 'hour').format("YYYY-MM-DD HH:mm:ss"),
                            filterDropdownItems: this.rows,
                            trigger: 'enter',
                        },
                    },
                    {
                        label: 'Period end',
                        field: 'gitTimestampForEndDate',
                        type: 'datetime',
                        width: '195px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Type Commit Time',
                            filterValue: moment().format("YYYY-MM-DD HH:mm:ss"),
                            filterDropdownItems: this.rows,
                            trigger: 'enter',
                        },
                    },
                ],

                // Datepicker options
                startDate: moment().subtract(6, 'days'),
                endDate: moment(),
                opens: "left",// which way the picker opens, default "center", can be "left"/"right"
                locale: {
                    direction: 'ltr', // direction of text
                    format: 'DD-MM-YYYY', // format of the dates displayed
                    separator: ' - ', // separator between the two ranges
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    weekLabel: 'W',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: moment.weekdaysMin(), // array of days - see moment documentations for details
                    monthNames: moment.monthsShort(), // array of month names - see moment documentations for details
                    firstDay: 1, // ISO first day of week - see moment documentations for details
                    showWeekNumbers: true // show week numbers on each row of the calendar
                },
                ranges: { // default value for ranges object (if you set this to false ranges will no be rendered)
                    '30min ago': [moment().subtract(30, 'minutes'), moment()],
                    '1h ago': [moment().subtract(1, 'hour'), moment()],
                    '2h ago': [moment().subtract(2, 'hour'), moment()],
                    '5h ago': [moment().subtract(5, 'hour'), moment()],
                    '24h ago': [moment().subtract(1, 'day'), moment()],
                    'Today': [moment().startOf('day'), moment()],
                    /*'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last week': [moment().subtract(1, 'week').startOf('week').add(1, 'days'),
                                    moment().subtract(1, 'week').endOf('week').add(1, 'days')],
                    'This month': [moment().startOf('month'), moment().endOf('month')],
                    'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'This year': [moment().startOf('year'), moment().endOf('year')],*/
                },
            };
        },

        computed: {
            ...mapGetters([
                'courseId'
            ]),

            jsonData() {
                this.search.changed = true; // Changing data to call method in computed section.
                return this.$refs.reportTable ? this.$refs.reportTable.filteredRows[0].children : [];
            },
        },

        methods: {
            setSubmissionsForReport(submissionsForReport) {
                this.submissionsForReport = submissionsForReport.rows.map(submission => {
                    return {
                        submissionId: submission.id,
                        firstName: submission.firstname,
                        lastName: submission.lastname,
                        exerciseName: submission.name,
                        submissionResult: submission.submission_result,
                        submissionTestsSum: submission.submission_tests_sum,
                        submissionTotal: submission.finalgrade,
                        isConfirmed: submission.confirmed,
                        gitTimestampForStartDate: submission.git_timestamp,
                        gitTimestampForEndDate: submission.git_timestamp
                    };
                });
                this.totalRecords = submissionsForReport.totalRecords;
                this.rows = this.submissionsForReport;
            },

            formatSubmissionResult(value) {
                return '| ' + value + ' |';
            },

            resetFilters() {
                this.$refs.reportTable.columnFilters.firstName = '';
                this.$refs.reportTable.columnFilters.lastName = '';
                this.$refs.reportTable.columnFilters.exerciseName = '';
                this.$refs.reportTable.columnFilters.isConfirmed = '';
                this.$refs.reportTable.columnFilters.gitTimestampForStartDate = moment().subtract(1, 'hour').format("YYYY-MM-DD HH:mm:ss");
                this.$refs.reportTable.columnFilters.gitTimestampForEndDate = moment().format("YYYY-MM-DD HH:mm:ss");
                this.loadItems();
            },

            updatePeriodDates(value) {
                if (value != null) {
                    this.$refs.reportTable.columnFilters.gitTimestampForStartDate = moment(value.startDate).format("YYYY-MM-DD HH:mm:ss");
                    this.$refs.reportTable.columnFilters.gitTimestampForEndDate = moment(value.endDate).format("YYYY-MM-DD HH:mm:ss");

                    this.loadItems();
                }
            },

            updateParams(newProps) {
                this.serverParams = Object.assign({}, this.serverParams, newProps);
            },

            onPageChange(params) {
                this.updateParams({page: params.currentPage});
                this.loadItems();
            },

            onPerPageChange(params) {
                if (this.serverParams.perPage !== params.currentPerPage) {
                    this.updateParams({perPage: params.currentPerPage});
                    this.loadItems();
                }
            },

            onSortChange(params) {
                this.updateParams({
                    sort: {
                        type: params[0].type,
                        field: params[0].field,
                    },
                });
                this.loadItems();
            },

            onColumnFilter(params) {
                this.search = params;
                this.updateParams(params);
                this.loadItems();
            },

            loadItems() {
                Submission.findAllSubmissionsForReport(this.courseId, this.serverParams, this.columns, this.setSubmissionsForReport)
            },
        },
    };
</script>
