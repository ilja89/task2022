<template>
    <div>
        <div v-for="testSuite in submission['test_suites']">
            <h2>Test results for {{testSuite['name']}}</h2>
            <br>
            <table>
                <tr>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'name')" class="sortable">Name</th>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'status')" class="sortable">Status</th>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'weight')" class="sortable">Weight</th>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'time')" class="sortable">Time</th>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'exception_class')" class="sortable">Exception class</th>
                    <th v-on:click="sortTable(testSuite['id'], testSuite['unit_tests'], 'exception_message')" class="sortable">Exception message</th>
                </tr>
                <tr v-for="unitTest in testSuite['unit_tests']">
                    <th>{{unitTest['name']}}</th>
                    <th>{{unitTest['status']}}</th>
                    <th>{{unitTest['weight']}}</th>
                    <th>{{unitTest['time_elapsed']}}</th>
                    <th>{{unitTest['exception_class']}}</th>
                    <th>{{unitTest['exception_message']}}</th>
                </tr>
            </table>
            <br>
            Number of tests: {{testSuite['unit_tests'].length}}<br><br>
            Passed tests: {{testSuite['passed_count']}}<br><br>
            Total weight: {{testSuite['weight']}}<br><br>
            Passed weight: {{getPassedWeight(testSuite)}}<br><br>
            Percentage: {{testSuite['grade']}}%<br>
            <br><br><br>
        </div>
        <div v-if="submission['test_suites'].length > 1">
            <h1>Overall</h1>
            Total number of tests: {{getTotalNumberOfTests()}}<br><br>
            Total passed tests: {{getTotalPassedNumberOfTests()}}<br><br>
            Total weight: {{getTotalWeight()}}<br><br>
            Total passed weight: {{getTotalPassedWeight()}}<br><br>
            Total percentage: {{getTotalPercentage()}}%<br><br>
        </div>
    </div>
</template>

<script>

    export default {
        props: {submission: {required: true}},

        data() {
             return {
                 previous_param: null,
                 current_param: null,
                 previous_table_id: null,
                 current_table_id: null,
                 show_stack_trace: false
             }
        },

        methods: {
            sortTable(table_id, list, param) {
                this.current_param = param
                this.current_table_id = table_id
                if (this.previous_param === this.current_param && this.previous_table_id === this.current_table_id) {
                    list.reverse();
                } else {
                    list.sort(this.compare);
                }
            },
            compare(a, b) {
                let paramMap = {name: 'name', status: 'status', weight: 'weight', time: 'time_elapsed',
                    exception_class: 'exception_class', exception_message: 'exception_message'}
                let stringA = a[paramMap[this.current_param]];
                let stringB = b[paramMap[this.current_param]];
                if (this.current_param === "name" || this.current_param === "status" ||
                    this.current_param === "exception_class" || this.current_param === "exception_message") {
                    if (stringA !== null) {
                        stringA = stringA.toUpperCase();
                    } else {
                        stringA = '~';
                    }
                    if (stringB !== null) {
                        stringB = stringB.toUpperCase();
                    } else {
                        stringB = '~';
                    }
                }

                let comparison = 0;
                if (stringA > stringB) {
                    comparison = 1;
                } else if (stringA < stringB) {
                    comparison = -1;
                }

                this.previous_param = this.current_param
                this.previous_table_id = this.current_table_id
                return comparison;
            },
            getPassedWeight(testSuite) {
                let total = 0
                for (let i = 0; i < testSuite['unit_tests'].length; i++) {
                    if (testSuite['unit_tests'][i]['status'] === 'PASSED') {
                        total += testSuite['unit_tests'][i]['weight']
                    }
                }
                return total
            },
            getTotalNumberOfTests() {
                let count = 0
                for (let i = 0; i < this.submission['test_suites'].length; i++) {
                    count += this.submission['test_suites'][i]['unit_tests'].length
                }
                return count
            },
            getTotalPassedNumberOfTests() {
                let count = 0
                for (let i = 0; i < this.submission['test_suites'].length; i++) {
                    for (let j = 0; j < this.submission['test_suites'][i]['unit_tests'].length; j++) {
                        if (this.submission['test_suites'][i]['unit_tests'][j]['status'] === 'PASSED') {
                            count++;
                        }
                    }
                }
                return count
            },
            getTotalWeight() {
                let weight = 0
                for (let i = 0; i < this.submission['test_suites'].length; i++) {
                    for (let j = 0; j < this.submission['test_suites'][i]['unit_tests'].length; j++) {
                        weight +=  this.submission['test_suites'][i]['unit_tests'][j]['weight']
                    }
                }
                return weight
            },
            getTotalPassedWeight() {
                let weight = 0
                for (let i = 0; i < this.submission['test_suites'].length; i++) {
                    for (let j = 0; j < this.submission['test_suites'][i]['unit_tests'].length; j++) {
                        if (this.submission['test_suites'][i]['unit_tests'][j]['status'] === 'PASSED') {
                            weight += this.submission['test_suites'][i]['unit_tests'][j]['weight']
                        }
                    }
                }
                return weight
            },
            getTotalPercentage() {
                return (this.getTotalPassedWeight() * 100 / this.getTotalWeight()).toFixed(2)
            }
        }
    }
</script>

<style scoped>
    table {
        background-color: #424242;
        color: #fff;
        border-radius: 2px;
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        max-width: 100%;
        padding: 0 24px;
        text-align: left!important;

     }
    .sortable {
        cursor: pointer;
        outline: 0;
        color: lightblue;
        font-weight: 300;
        font-size: 16px;
    }
    .sortable:active {
        color: #3e95df;
    }
    th {
        padding: 0 24px;
        line-height: 45px;
    }
    tr {
        border: solid;
        border-width: 1px 0;
        border-color: #2b666c;
    }
</style>