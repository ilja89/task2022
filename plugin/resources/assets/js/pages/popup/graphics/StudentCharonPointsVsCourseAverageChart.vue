<template>
    <popup-section
            title="Graph"
            subtitle="Here are student confirmed exercise points compared to course average"
    >

        <template slot="header-right">
            <v-btn class="ma-2" tile outlined color="primary" @click="toggle()">{{ buttonText }}</v-btn>
        </template>

        <apexcharts height="750" :options="options" :series="series" v-show="isOpen"></apexcharts>

    </popup-section>
</template>

<script>
    import {mapGetters} from 'vuex';
    import VueApexCharts from 'vue-apexcharts';
    import {PopupSection} from '../layouts';
    import {Submission} from '../../../api';

    export default {
        components: {PopupSection, 'apexcharts': VueApexCharts},
        props: ['student', 'charons', 'averageSubmissions'],

        data() {
            return {
                isOpen: false,
                results: [],
                submissionNames: [],
                courseAverageGrades: [],
                courseMaximumGrades: [],
                finalGrades: [],
            };
        },

        computed: {
            ...mapGetters([
                'courseId',
            ]),

            buttonText() {
                return this.isOpen ? 'Hide chart' : 'Show chart';
            },

            sumStudentGrades() {
                let calculatedStudentCharonResult = [];
                let sumResult = 0;

                for (let i = 0; i < this.finalGrades.length; i++) {
                    sumResult += this.finalGrades[i];
                    sumResult = +sumResult.toFixed(2);
                    calculatedStudentCharonResult.push(sumResult);
                }
                return calculatedStudentCharonResult;
            },

            sumAverage() {
                let calculatedStudentCharonResult = [];
                let sumResult = 0;

                for (let i = 0; i < this.courseAverageGrades.length; i++) {
                    sumResult += +this.courseAverageGrades[i];
                    sumResult = +sumResult.toFixed(2);
                    calculatedStudentCharonResult.push(sumResult);
                }
                return calculatedStudentCharonResult;
            },

            sumMaximum() {
                let calculatedStudentCharonResult = [];
                let sumResult = 0;

                for (let i = 0; i < this.courseMaximumGrades.length; i++) {
                    sumResult += +this.courseMaximumGrades[i];
                    sumResult = +sumResult.toFixed(2);
                    calculatedStudentCharonResult.push(sumResult);
                }
                return calculatedStudentCharonResult;
            },

            options() {
                return {
                    chart: {
                        type: 'line',
                        shadow: {
                            enabled: true,
                            color: '#000',
                            top: 18,
                            left: 7,
                            blur: 10,
                            opacity: 1
                        },
                        /*toolbar: {
                            show: false
                        }*/
                    },
                    colors: ['#59c2e6', '#4f5f6f', '#ff8c00'],
                    dataLabels: {
                        enabled: true
                    },
                    /*stroke: {
                        curve: "smooth"
                    },*/
                    /*title: {
                        text: ""
                    },*/
                    xaxis: {
                        categories: this.submissionNames,
                        title: {
                            text: 'Exercises',
                            style: {
                                fontSize: '20px'
                            },
                        },
                    },
                    yaxis: {
                        title: {
                            text: 'Points',
                            style: {
                                fontSize: '20px'
                            },
                        },
                    },
                };
            },

            series() {
                return [
                    {
                        name: this.student ? this.student.firstname + " " + this.student.lastname : "",
                        data: this.sumStudentGrades
                    },
                    {
                        name: "Course Average",
                        data: this.sumAverage
                    },
                    {
                        name: "Course Maximum",
                        data: this.sumMaximum
                    },
                ];
            },
        },

        methods: {
            toggle() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    Submission.findByUser(this.courseId, this.student.id, this.getStudentConfirmedSubmissions);
                }
            },

            getStudentConfirmedSubmissions(data) {
                this.submissionNames = data.map(submission => submission.name);
                this.finalGrades = data.map(submission => Number(submission.finalgrade));
                this.courseAverageGrades = this.averageSubmissions.map(averageSubmissions => Number(averageSubmissions.course_average_finalgrade));
                this.courseMaximumGrades = this.averageSubmissions.map(averageSubmissions => Number(averageSubmissions.grademax));
            },
        }
    };
</script>
