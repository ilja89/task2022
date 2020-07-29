<template>
    <div class="labs-row is-flex">
        <div class="labs-field">
            <label class="required">Start</label>
            <p class="input-helper-labs">Start date and time.</p>
            <datepicker :datetime="lab_given.start"></datepicker>
            <input type="hidden" :value="lab_given.start">
        </div>

        <div class="labs-field">
            <label class="required">End</label>
            <p class="input-helper-labs">End time.</p>
            <datepicker :to_be_checked="true" :datetime="lab_given.end"></datepicker>
            <input type="hidden" :value="lab_given.end">
        </div>

        <div class="labs-field is-flex-1">
            <label >Teachers</label>

            <p class="input-helper-labs">Teachers attending this lab session.</p>

            <multiselect v-model="lab_given.teachers" :options="teachers" :multiple="true" label="full_name"
                         :close-on-select="false" placeholder="Select teachers" trackBy="id"
                         :clear-on-select="true" style="width: 300px">
            </multiselect>

        </div>

    </div>
</template>

<script>
    import { Datepicker } from '../../../../../components/partials';
    import { Translate } from '../../../../../mixins';
    import { CharonSelect } from '../../../../../components/form';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect, Multiselect },

        props: {
            lab_given: {required: true},
            teachers: {required: true}
        }
    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
