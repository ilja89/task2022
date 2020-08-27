<template>

    <fieldset class="clearfix collapsible" id="id_modstandardelshdr">
        <div v-if="show_info" class="topic-open">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Charon defense info</legend>
        </div>
        <div v-else class="topic-closed">
            <legend v-on:click="show_info = !show_info" class="ftoggler">Charon defense info</legend>
        </div>

        <div v-if="show_info" class="fcontainer clearfix fitem">
            <div class="defense-row is-flex">
                <div class="defense-field">
                    <label>Deadline</label>
                    <p class="input-helper">Defense deadline.</p>
                    <datepicker :datetime="charon.defense_deadline"></datepicker>
                    <input type="hidden" :value="charon.defense_deadline">
                </div>

                <div class="defense-field">
                    <label>Duration</label>
                    <p class="input-helper">Defense duration in minutes.</p>
                    <input type="number"
                           v-model="charon.defense_duration"
                           class="form-control">
                </div>

                <div class="defense-field">
                    <label>Labs</label>

                    <p class="input-helper">Labs where this Charon can be defended.</p>

                    <multiselect v-model="charon.charonDefenseLabs" :options="labs" :multiple="true" label="name"
                                 :close-on-select="false" placeholder="Select labs" trackBy="id"
                                 :clear-on-select="true" class="multiselect__width">
                    </multiselect>

                </div>

                <div class="defense-field">
                    <label>Threshold</label>

                    <p class="input-helper">Minimum percentage to register for defense.</p>
                    <input type="number"
                            v-model="charon.defense_threshold"
                            class="form-control" min="0" max="100">
                </div>

                <div class="defense-field">
                    <label>Teacher</label>
                    <br>
                    <input v-model="charon.choose_teacher" type="checkbox">
                    <a class="input-helper checkbox-text choose_teacher">Student can choose a teacher.</a>
                </div>

            </div>
        </div>

    </fieldset>

</template>

<script>
    import {Datepicker} from "../../../components/partials";
    import Multiselect from "vue-multiselect";

    export default {
        components: { Datepicker, Multiselect },
        props: {
            charon: { required: true },
            labs: { required: true}
        },
        data() {
            return {
                show_info: true
            }
        }
    }
</script>

<style scoped>

    .datepicker-overlay .cov-date-box .hour-item,
    .datepicker-overlay .cov-date-box .min-item {
        padding: 0 10px;
    }

    .defense-row {
        -ms-flex-align: end;
        align-items: flex-end;
    }

    .defense-row:not(:first-child) .defense-field .input-helper {
        visibility: hidden;
        height: 0;
        margin: 0;
    }

    .defense-field {
        padding: 10px 15px;
    }

    .defense-field .input-helper {
        padding-left: 0;
        padding-right: 0;
    }

    .defense-field .checkbox-text {
        display: inline;
        margin-left: 4px;
        color: #5e6977 !important;
    }

    .defense-field .multiselect__width {
        max-width: 275px;
    }

    .form-control {
        display: block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: .9375rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0;
        -webkit-transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        -o-transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }

    .choose_teacher {
        cursor: default;
    }

</style>