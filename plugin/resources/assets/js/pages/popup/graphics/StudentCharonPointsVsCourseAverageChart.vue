<template>
    <popup-section
            title="Graph"
            subtitle="Here are student confirmed exercise points compared to course average"
    >

        <template slot="header-right">
            <button class="button is-primary" @click='toggle()'>{{ buttonText }}</button>
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
                studentConfirmedPoints: [],
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

                for (let i = 0; i < this.studentConfirmedPoints.length; i++) {
                    sumResult += this.studentConfirmedPoints[i];
                    sumResult = +sumResult.toFixed(2);
                    calculatedStudentCharonResult.push(sumResult);
                }
                return calculatedStudentCharonResult;
            },

            sumAverage() {
                let calculatedStudentCharonResult = [];
                let sumResult = 0;

                for (let i = 0; i < this.averageSubmissions.length; i++) {
                    sumResult += +this.averageSubmissions[i].average_calc_result;
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
                    colors: ['#59c2e6', '#4f5f6f'],
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
                        categories: this.charons.map(c => c.name),
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
                    /*{
                        name: "To pass course",
                        // Data should contain method that calculates points to pass course cumulatively
                        data:
                    },*/
                ];
            },
        },

        methods: {
            toggle() {
                this.isOpen = !this.isOpen;
            },
            getStudentConfirmedSubmissions(data) {
                const confirmedSubmissionsIds = data.map(submission => submission.charon_id);
                this.studentConfirmedPoints = this.averageSubmissions.map(charon => {
                    if (confirmedSubmissionsIds.includes(charon.id)) {
                        return Number(data.find(s => s.charon_id === charon.id).calculated_result);
                    }
                    return 0;
                });
            },
        },
        watch: {
            student: {
                immediate: true,
                handler() {
                    Submission.findByUser(this.courseId, this.student.id, this.getStudentConfirmedSubmissions);
                },
            },
        },
    };
</script>
