<template>
    <div class="deadline-row is-flex">
        <div class="deadline-field">
            <label>Start</label>
            <p class="input-helper">Start date and time.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :name="'deadlines[' + id + '][deadline_time]'" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field">
            <label>End</label>
            <p class="input-helper">End date.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :name="'deadlines[' + id + '][deadline_time]'" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field is-flex-1">
            <label>Teachers</label>

            <p class="input-helper">Teachers attending this lab session.</p>

            <select v-model="deadline.teachers">
                <option v-for="teacher in teachers" :value="teacher.id">
                    {{ teacher.name }}
                </option>
            </select>

        </div>

    </div>
</template>

<script>
    import { Datepicker } from '../../../components/partials';
    import { Translate } from '../../../mixins';
    import { CharonSelect } from '../../../components/form';

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect },

        props: {
            deadline: { required: true },
            teachers: { required: true },
        }
    }
</script>

