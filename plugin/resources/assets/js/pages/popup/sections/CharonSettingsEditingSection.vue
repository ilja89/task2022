<template>

    <v-form>
        <v-container>
            <v-row>

                <v-col cols="12" sm="6" md="3">
                    <div>
                        <label>Deadline</label>
                        <p class="input-helper">Defense deadline</p>
                        <datepicker :datetime="charon.defense_deadline"></datepicker>
                        <input type="hidden" :value="charon.defense_deadline">
                    </div>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <p>Minimum percentage to register for defense</p>
                    <v-slider
                            v-model="charon.defense_duration"
                            color="purple"
                            label="Duration"
                            min="0"
                            max="30"
                            thumb-label
                    ></v-slider>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <p>Minimum percentage to register for defense</p>
                    <v-slider
                            v-model="charon.defense_threshold"
                            color="purple"
                            label="Threshold"
                            min="0"
                            max="100"
                            thumb-label
                    ></v-slider>
                </v-col>

                <v-col cols="12" sm="6" md="3">
                    <v-container class="px-0" fluid>
                        <v-switch
                                v-model="charon.choose_teacher"
                                label="Student can choose a teacher"
                        ></v-switch>
                    </v-container>
                </v-col>

                <v-col cols="12" sm="12" md="6">
                    <label>Labs</label>
                    <p>Labs where this Charon can be defended</p>
                    <multiselect v-model="charon.charonDefenseLabs" :options="labs" :multiple="true" label="name"
                                 :close-on-select="false" placeholder="Select labs" trackBy="id"
                                 :clear-on-select="true" class="multiselect__width">
                    </multiselect>
                </v-col>

            </v-row>
        </v-container>
    </v-form>

</template>

<script>
    import {Datepicker} from "../../../components/partials";
    import Multiselect from "vue-multiselect";

    export default {
        components: {Datepicker, Multiselect},
        props: {
            charon: {required: true},
            labs: {required: true}
        },
        data() {
            return {
                show_info: true
            }
        }
    }
</script>

<style>
    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 10px 10px;
    }
</style>