<template>

    <popup-section
            title="Students report"
            subtitle="Search in all students submissions in this course."
    >
        <div>
            <vue-good-table ref="reportTable"
                :columns="columns"
                :rows="rows"
                :fixed-header="true"
                :line-numbers="false"
                :search-options="{
                    enabled: false
                    //trigger: 'enter'
                }"
                :sort-options="{
                    enabled: true,
                    initialSortBy: [
                        {field: 'gitTimestampForEndDate', type: 'desc'}
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
                @on-column-filter="onColumnFilter"
            >
                <div slot="table-actions">
                    <button class="button is-primary" @click="resetFilters">
                        Reset Filters
                    </button>

                    <date-range-picker
                        :startDate="startDate"
                        :endDate="endDate"
                        :locale-data="locale"
                        :opens="opens"
                        :ranges="ranges"
                        @update="updatePeriodDates"
                    >
                        <!--Optional scope for the input displaying the dates -->
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
                                   submissionTotal: { title: 'Submission Total' },
                                   isConfirmed: { title: 'Is Confirmed' },
                                   gitTimestampForStartDate: { title: 'Git Commit' }
                        }"
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
                submissionsForReport: [],
                columns: [
                    {
                        label: 'First Name',
                        field: 'firstName',
                        filterOptions: {
                            enabled: true, // enable filter for this column
                            placeholder: 'Filter First Name', // placeholder for filter input
                            filterValue: '', // initial populated value for this filter
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
                            filterValue: '',
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                    {
                        label: 'Exercise Name',
                        field: 'exerciseName',
                        width: '155px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter Exercises',
                            filterValue: '',
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                    {
                        label: 'Submission Result',
                        field: 'submissionResult',
                        width: '190px',
                        formatFn: this.formatSubmissionResult
                    },
                    {
                        label: 'Submission Total',
                        field: 'submissionTotal',
                        width: '170px',
                    },
                    {
                        label: 'Is Confirmed',
                        field: 'isConfirmed',
                        type: 'number',
                        width: '180px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter (un)confirmed',
                            filterValue: '',
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                    {
                        label: 'Period Start',
                        field: 'gitTimestampForStartDate',
                        type: 'datetime',
                        width: '195px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter Commit',
                            filterValue: moment().subtract(1, 'hour').format("YYYY-MM-DD HH:mm:ss"),
                            filterFn: this.startDateFilter,
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                    {
                        label: 'Period end',
                        field: 'gitTimestampForEndDate',
                        type: 'datetime',
                        width: '195px',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Filter Commit',
                            filterValue: moment().format("YYYY-MM-DD HH:mm:ss"),
                            filterFn: this.endDateFilter,
                            filterDropdownItems: this.submissionsForReport,
                        },
                    },
                ],
                search: {}, // Object is needed for filtered data export.

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
                    '1h ago' : [moment().subtract(1, 'hour'), moment()],
                    '2h ago' : [moment().subtract(2, 'hour'), moment()],
                    '5h ago' : [moment().subtract(5, 'hour'), moment()],
                    '24h ago' : [moment().subtract(1, 'day'), moment()],
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

            rows() {
                return this.submissionsForReport;
            },

            jsonData() {
                this.search.changed = true; // Changing data to call method in computed section.
                return this.$refs.reportTable ? this.$refs.reportTable.filteredRows[0].children : [];
            },
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
                        gitTimestampForStartDate: submission.git_timestamp,
                        gitTimestampForEndDate: submission.git_timestamp
                    };
                });
            },

            formatSubmissionResult(value) {
                return '| ' + value + ' |';
            },

            onColumnFilter(searchValue) {
                this.search = searchValue;
            },

            resetFilters() {
                this.$refs.reportTable.columnFilters.firstName = '';
                this.$refs.reportTable.columnFilters.lastName = '';
                this.$refs.reportTable.columnFilters.exerciseName = '';
                this.$refs.reportTable.columnFilters.isConfirmed = '';
                this.$refs.reportTable.columnFilters.gitTimestampForStartDate = moment().subtract(1, 'hour').format("YYYY-MM-DD HH:mm:ss");
                this.$refs.reportTable.columnFilters.gitTimestampForEndDate = moment().format("YYYY-MM-DD HH:mm:ss");
            },

            startDateFilter(data, filterString) {
                return (data = Date.parse(data) >= Date.parse(filterString));
            },

            endDateFilter(data, filterString) {
                return (data = Date.parse(data) <= Date.parse(filterString));
            },

            updatePeriodDates(value) {
                this.$refs.reportTable.columnFilters.gitTimestampForStartDate = moment(value.startDate).format("YYYY-MM-DD HH:mm:ss");
                this.$refs.reportTable.columnFilters.gitTimestampForEndDate = moment(value.endDate).format("YYYY-MM-DD HH:mm:ss");

                this.startDateFilter(this.submissionsForReport, this.$refs.reportTable.columnFilters.gitTimestampForStartDate);
                this.endDateFilter(this.submissionsForReport, this.$refs.reportTable.columnFilters.gitTimestampForEndDate);
            },
        },

        created() {
            Submission.findAllSubmissionsForReport(this.courseId, this.setSubmissionsForReport);
        },
    };
</script>
