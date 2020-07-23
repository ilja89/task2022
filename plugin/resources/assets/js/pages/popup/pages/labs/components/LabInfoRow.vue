<template>
    <div class="labs-row is-flex">
        <div class="labs-field">
            <label class="required">Start</label>
            <p class="input-helper-labs">Start date and time.</p>
            <datepicker :datetime="form.start"></datepicker>
            <input type="hidden" :value="form.start">
        </div>

        <div class="labs-field">
            <label class="required">End</label>
            <p class="input-helper-labs">End time.</p>
            <datepicker :datetime="form.end"></datepicker>
            <input type="hidden" :value="form.end">
        </div>

        <div class="labs-field is-flex-1">
            <label class="required">Teachers</label>

            <p class="input-helper-labs">Teachers attending this lab session.</p>

            <multiselect v-model="deadline.teachers" :options="teachers" :multiple="true" label="name"
                         :close-on-select="false" placeholder="Select teachers" trackBy="name"
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
    import Lab from "../../../../../api/Lab";

    export default {
        mixins: [ Translate ],

        components: { Datepicker, CharonSelect, Multiselect },

        data() {
            return {
                deadline: {teachers: []}
            }
        },

        props: {
            teachers: { required: true },
            form: { required: true}
        }
    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
