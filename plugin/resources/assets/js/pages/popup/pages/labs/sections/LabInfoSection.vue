<template>
    <v-card class="mx-auto mb-16" outlined light raised>
        <v-container class="spacing-playground pa-3" fluid>

            <v-form>
                <v-container>
                    <v-row>
                        <v-col cols="12" sm="6" md="6" lg="6">
                            <div class="labs-field">
                                <p>Start date and time</p>
                                <datepicker :datetime="lab_given.start"></datepicker>
                                <input type="hidden" :value="lab_given.start">
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6" md="6" lg="6">
                            <div class="labs-field">
                                <p>End time</p>
                                <datepicker :datetime="lab_given.end"></datepicker>
                                <input type="hidden" :value="lab_given.end">
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6" md="6" lg="6">
                            <div class="labs-field is-flex-1">
                                <p>Teachers attending this lab session</p>
                                <multiselect v-model="lab_given.teachers" :options="teachers" :multiple="true" label="fullname"
                                             :close-on-select="false" placeholder="Select teachers" trackBy="id"
                                             :clear-on-select="true">
                                </multiselect>
                            </div>
                        </v-col>

                        <v-col cols="12" sm="6" md="6" lg="6">
                            <div class="labs-field is-flex-1">
                                <p>Defendable Charons during this lab</p>
                                <multiselect v-model="lab_given.charons" :options="charons" :multiple="true" label="project_folder"
                                             :close-on-select="false" placeholder="Select charons" trackBy="id"
                                             :clear-on-select="true">
                                </multiselect>
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </v-form>

        </v-container>
    </v-card>
</template>

<script>
    import {Translate} from '../../../../../mixins';
    import {Datepicker} from "../../../../../components/partials";
    import {CharonSelect} from "../../../../../components/form";
    import Multiselect from "vue-multiselect";

    export default {
        mixins: [Translate],

        components: {Datepicker, CharonSelect, Multiselect},

        data() {
            return {
                show_info: true,
            }
        },

        props: {
            lab_given: {required: true},
            teachers: {required: true},
            charons: {required: true}
        },

        computed: {
            isEditing() {
                return window.isEditing;
            }
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
