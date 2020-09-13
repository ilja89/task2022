<template>
    <v-form>
        <v-container>
            <v-row>

                <v-col cols="12" sm="6" md="6" lg="4">
                    <div class="labs-field">
                        <p>Start date and time</p>
                        <datepicker :datetime="lab_given.start"></datepicker>
                        <input type="hidden" :value="lab_given.start">
                    </div>
                </v-col>

                <v-col cols="12" sm="6" md="6" lg="4">
                    <div class="labs-field">
                        <p>End time</p>
                        <datepicker :to_be_checked="true" :datetime="lab_given.end"></datepicker>
                        <input type="hidden" :value="lab_given.end">
                    </div>
                </v-col>

                <v-col cols="12" sm="12" md="12" lg="4">
                    <div class="labs-field is-flex-1">
                        <p>Teachers attending this lab session</p>
                        <multiselect v-model="lab_given.teachers" :options="teachers" :multiple="true" label="full_name"
                                     :close-on-select="false" placeholder="Select teachers" trackBy="id"
                                     :clear-on-select="true" style="width: 300px">
                        </multiselect>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </v-form>

</template>

<script>
    import {Datepicker} from '../../../../../components/partials';
    import {Translate} from '../../../../../mixins';
    import {CharonSelect} from '../../../../../components/form';
    import Multiselect from 'vue-multiselect';

    export default {
        mixins: [Translate],

        components: {Datepicker, CharonSelect, Multiselect},

        props: {
            lab_given: {required: true},
            teachers: {required: true}
        }
    }
</script>

<style src="../../../../../../../../../node_modules/vue-multiselect/dist/vue-multiselect.min.css"></style>

<style>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

</style>
