<template>
    <div class="">
        <date-picker :limit="limit" :date="datetime" :option="option"></date-picker>
    </div>
</template>

<script>
    import DatePicker from 'vue-datepicker'
    import moment from 'moment';
    import {mapState} from "vuex";

    export default {

        components: {DatePicker},

        props: {
            datetime: {required: true},
            to_be_checked: {required: false}
        },

        data() {
            return {
                startTime: {
                    time: ''
                },
                endtime: {
                    time: ''
                },

                option: {
                    type: 'min',
                    week: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
                    month: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    format: 'YYYY-MM-DD HH:mm',
                    placeholder: this.placeholder,
                    buttons: {
                        ok: 'Ok',
                        cancel: 'Cancel'
                    },
                    inputStyle: {
                        'height': '46px',
                        'padding': '8px 12px',
                        'border': '1px solid #dadada',
                        '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                        'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    }
                },
            }
        },

        computed: {

            ...mapState([
                'lab'
            ]),
            limit: function () {
                let limit = [{
                    type: 'fromto',
                    from: moment().subtract(1, 'days').format("YYYY-MM-DD"),
                    to: ''
                }]
                if (this.to_be_checked && this.lab.start.time) {

                    let correctForm
                    if (this.lab.start.time.toString().includes('GMT')) {
                        let month = ("0" + (new Date(this.lab.start.time).getMonth() + 1).toString()).substr(-2, 2)
                        let day = ("0" + new Date(this.lab.start.time).getDate().toString()).substr(-2, 2)
                        correctForm = (new Date(this.lab.start.time).getUTCFullYear()).toString() + '-'
                            + month + '-' + day
                    } else {
                        correctForm = this.lab.start.time.toString().substr(0, 10);
                    }
                    let millis_in_a_day = 60 * 60 * 24 * 1000
                    limit.push({
                        type: 'fromto',
                        from: new Date(new Date(correctForm) - millis_in_a_day),
                        to: new Date(new Date(correctForm) + millis_in_a_day)
                    })
                }
                if (this.to_be_checked && !this.lab.start.time) {
                    limit.push({
                        type: 'fromto',
                        from: '',
                        to: moment().subtract(1, 'days').format("YYYY-MM-DD")
                    })
                }
                return limit
            }
        }
    }
</script>
