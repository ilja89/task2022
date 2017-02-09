<template>
    <div class="fcontainer clearfix fitem">
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

                <grademap-row
                        :grademap="grademap">
                </grademap-row>

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
    import CharonSelect from '../form/CharonSelect.vue';
    import GradesCheckboxes from '../form/GradesCheckboxes.vue';
    import CharonTabs from '../partials/CharonTabs.vue';
    import CharonTab from '../partials/CharonTab.vue';
    import GrademapRow from './GrademapRow.vue';
    import CharonTextInput from '../form/CharonTextInput.vue';
    import CharonNumberInput from '../form/CharonNumberInput.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonSelect, GradesCheckboxes, CharonTabs, CharonTab, GrademapRow, CharonTextInput, CharonNumberInput },

        props: {
            form: { required: true }
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
            }
        }
    }
</script>
