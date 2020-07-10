<template>
    <div class="deadline-row is-flex">
        <div class="deadline-field">
            <label>Start</label>
            <p class="input-helper">Start date and time.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field">
            <label>End</label>
            <p class="input-helper">End date.</p>
            <datepicker :datetime="deadline.deadline_time"></datepicker>
            <input type="hidden" :value="deadline.deadline_time.time">
        </div>

        <div class="deadline-field is-flex-1">
            <label>Teachers</label>

            <p class="input-helper">Teachers attending this lab session.</p>

            <multiselect v-model="deadline.teachers" :options="teachers" :multiple="true" label="name"
                         :close-on-select="false" placeholder="Select teachers" trackBy="name"
                         :clear-on-select="true" style="width: 300px">
            </multiselect>

        </div>

    </div>
</template>

<script>
    import { Datepicker } from '../../../components/partials';
    import { Translate } from '../../../mixins';
    import { CharonSelect } from '../../../components/form';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect, Multiselect },

        props: {
            deadline: { required: true },
            teachers: { required: true },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

