<template>
    <div>

        <charon-select v-if="!isUpdate"
                :label="translate('preset_label')"
                name="preset"
                :options="form.presets"
                :selected="form.fields.preset !== null ? form.fields.preset.id : null"
                key_field="id"
                @input-was-changed="onPresetChanged">
        </charon-select>

        <charon-select
                :label="translate('grading_method_label')"
                name="grading_method"
                :options="form.grading_methods"
                :selected="form.fields.grading_method"
                @input-was-changed="onGradingMethodChanged" >
        </charon-select>

        <grades-checkboxes
                :label="translate('grades_label')"
                :grade_types="form.grade_types"
                :active_grade_type_codes="getActiveGradeTypes()"
                @grade-type-was-activated="onGradeTypeActivated"
                @grade-type-was-deactivated="onGradeTypeDeactivated">
        </grades-checkboxes>

        <charon-tabs v-if="form.fields.grademaps.length > 0">

            <charon-tab
                    v-for="(grademap, index) in form.fields.grademaps"
                    v-if="typeof grademap !== 'undefined'"
                    :name="getGradeTypeName(grademap.grade_type_code)"
                    :selected="index === 0 ? true : false">

                <grademap-row :grademap="grademap"></grademap-row>

            </charon-tab>

        </charon-tabs>

        <charon-number-input
                name="max_score"
                :required="true"
                :label="translate('max_points_label')"
                :input_value="form.fields.max_score"
                @input-was-changed="onMaxScoreChanged">
        </charon-number-input>

        <charon-text-input
                input_name="calculation_formula"
                :required="false"
                :input_label="translate('calculation_formula_label')"
                :input_value="form.fields.calculation_formula"
                @input-was-changed="onCalculationFormulaChanged">
        </charon-text-input>

    </div>
</template>

<script>
    import { CharonSelect, GradesCheckboxes, CharonTextInput, CharonNumberInput } from '../../../components/form';
    import { CharonTabs, CharonTab } from '../../../components/partials';

    import GrademapRow from '../components/GrademapRow.vue';

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
                let grade_name = '';

                this.form.grade_types.forEach((grade_type) => {
                    if (grade_type.code === grade_type_code) {
                        grade_name = grade_type.name;
                    }
                });

                return grade_name;
            },
        }
    }
</script>
