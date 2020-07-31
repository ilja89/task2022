<template>
    <div class="">
        <date-picker :date="datetime" :option="option" :limit="limit"></date-picker>
    </div>
</template>

<script>
    import DatePicker from 'vue-datepicker'
    import moment from 'moment';

    export default {

        components: { DatePicker },

        props: {
            datetime: { required: true },
            placeholder: {required: false},
            date: {required: false},
            to: {required: false}

        },

        data () {
            return {
                startTime: {
                    time: this.datetime
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
                limit: [
                    {
                        type: 'fromto',
                        from: moment().subtract(1, 'days').format("YYYY-MM-DD"),
                        to: this.to,
                    }
                ]
            }
        },

    }
</script>
