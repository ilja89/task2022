<template>
    <div>

        <charon-select v-if="!isUpdate"
                :label="translate('preset_label')"
                name="preset"
                :options="form.presets"
                :value="form.fields.preset !== null ? form.fields.preset.id : null"
                key_field="id"
                :helper_text="translate('preset_select_helper')"
                @input-was-changed="onPresetChanged">
        </charon-select>

        <charon-select
                :label="translate('grading_method_label')"
                name="grading_method"
                :options="form.grading_methods"
                :value="form.fields.grading_method"
                :helper_text="translate('grading_method_helper')"
                @input-was-changed="onGradingMethodChanged" >
        </charon-select>

        <grades-checkboxes
                :label="translate('grades_label')"
                :active_grade_type_codes="getActiveGradeTypes()"
                :helper_text="translate('grades_helper')"
                @grade-type-was-activated="onGradeTypeActivated"
                @grade-type-was-deactivated="onGradeTypeDeactivated">
        </grades-checkboxes>

        <charon-tabs v-if="form.fields.grademaps.length > 0">

            <charon-tab
                    v-for="(grademap, index) in form.fields.grademaps"
                    v-if="typeof grademap !== 'undefined'"
                    :name="getGradeTypeName(grademap.grade_type_code)"
                    :key="grademap.grade_type_code"
                    :selected="index === 0">
                <grademap-row :grademap="grademap" :formula="form.fields.calculation_formula"></grademap-row>

            </charon-tab>

        </charon-tabs>

        <charon-number-input
                input_class="is-quarter"
                name="max_score"
                :required="true"
                :label="translate('max_points_label')"
                :value="form.fields.max_score"
                :helper_text="translate('max_points_helper')"
                @input-was-changed="onMaxScoreChanged">
        </charon-number-input>

        <charon-text-input
                name="calculation_formula"
                :required="false"
                :label="translate('calculation_formula_label')"
                :value="form.fields.calculation_formula"
                :helper_text="translate('calculation_formula_helper')"
                @input-was-changed="onCalculationFormulaChanged">
        </charon-text-input>

    </div>
</template>

<script>
    import { CharonSelect, GradesCheckboxes, CharonTextInput, CharonNumberInput } from '../../../components/form';
    import { CharonTabs, CharonTab } from '../../../components/partials';
    import { GrademapRow } from '../components';
    import { Translate, EmitEventOnInputChange } from '../../../mixins';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect, GradesCheckboxes, CharonTabs, CharonTab, GrademapRow, CharonTextInput, CharonNumberInput },

        props: {
            form: { required: true }
        },

        computed: {
            isUpdate() {
                return window.isEditing;
            }
        },

        methods: {
            getActiveGradeTypes() {
                let active_grademaps = [];

                this.form.fields.grademaps.forEach((grademap) => {
                    active_grademaps.push(grademap.grade_type_code);
                });

                return active_grademaps;
            },

            getGradeTypeName(grade_type_code) {
                let gradeTypeName = '';
                if (grade_type_code <= 100) {
                    gradeTypeName = 'Tests_' + grade_type_code;
                } else if (grade_type_code <= 1000) {
                    gradeTypeName = 'Style_' + grade_type_code % 100;
                } else {
                    gradeTypeName = 'Custom_' + grade_type_code % 1000;
                }

                return gradeTypeName;
            },
        }
    }
</script>
