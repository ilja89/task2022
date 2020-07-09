<template>
    <div class="defense-row is-flex">
        <div class="defense-field">
            <label>{{ translate('deadline_label') }}</label>
            <p class="input-helper">Defense deadline.</p>
            <datepicker :datetime="defense.deadline"></datepicker>
            <input type="hidden" :value="defense.deadline.time">
        </div>

        <div class="defense-field">
            <label>Duration</label>
            <p class="input-helper">Defense duration in minutes.</p>
            <input type="number"

                   v-model="defense.duration"
                   class="form-control">
        </div>

        <div class="defense-field">
            <label>Labs</label>

            <p class="input-helper">Labs where this Charon can be defended.</p>

            <multiselect v-model="defense.labs" :options="labs" :multiple="true" label="name"
                         :close-on-select="false" placeholder="Select labs" trackBy="name"
                         :clear-on-select="true" class="multiselect__width">
            </multiselect>

        </div>

        <div class="defense-field">
            <label>Teacher</label>
            <br>
            <input v-model="defense.teacher" type="checkbox">
            <a class="input-helper checkbox-text">Student can choose a teacher.</a>
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
            defense: { required: true },
            labs: { required: true },
        },

    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

</style>
