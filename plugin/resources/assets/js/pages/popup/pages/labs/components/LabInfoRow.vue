<template>
    <div class="labs-row is-flex">
        <div class="labs-field">
            <label v-on:click="ehm" class="required">Start</label>
            <p class="input-helper-labs">Start date and time.</p>
            <datepicker :datetime="dd"></datepicker>
            <input type="hidden" :value="dd">
        </div>

        <div class="labs-field">
            <label class="required">End</label>
            <p class="input-helper-labs">End time.</p>
            <datepicker :datetime="ddd"></datepicker>
            <input type="hidden" :value="ddd">
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
                dd: {time: null},
                ddd: {time: null},
                lab: {
                    start: '12-12-2020 10:00',
                    end: '12-12-2020 11:30',
                    teachers: [{name: 'Ago Luberg'}, {name: 'Keegi Veel'}],  // full teacher object probably
                    weeks: [1, 4, 9, 10, 14, 15]
                }
            }
        },

        props: {
            deadline: { required: true },
            teachers: { required: true },
        },
        methods: {
            ehm() {
                console.log(this.dd)
                Lab.save('1', this.dd.time, this.ddd.time, lab => {  // works
                    VueEvent.$emit('show-notification', 'Lab saved!')
                });
            }
        }
    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>
