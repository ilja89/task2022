<template>
    <date-picker :date="startTime" :option="option" :limit="limit"></date-picker>
</template>

<script>
    import DatePicker from 'vue-datepicker'
    import moment from 'moment';

    export default {

        components: { DatePicker },

        data () {
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
                    format: 'DD-MM-YYYY HH:mm',
                    placeholder: 'Deadline',
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
                        to: '',
                    }
                ]
            }
        }
    }
</script>
