<template>

    <charon-fieldset>
        <template slot="title">{{ translate('presets_title') }}</template>

        <slot>

            <charon-select
                    :label="translate('edit_preset_label')"
                    name="active_preset"
                    :options="presets"
                    :selected="null"
                    key_field="id"
                    @input-was-changed="onActivePresetChanged">
            </charon-select>

            <a @click="createPreset" class="btn-link add-preset-btn">
                Or add a new preset
            </a>

            <div v-if="activePreset !== null">

                <charon-text-input
                        input_name="preset_name"
                        :required="false"
                        :input_label="translate('preset_name_label')"
                        :input_value="activePreset.name"
                        @input-was-changed="onNameChanged">
                </charon-text-input>

                <charon-text-input
                        input_name="preset_extra"
                        :required="false"
                        :input_label="translate('extra_label')"
                        :input_value="activePreset.extra"
                        @input-was-changed="onExtraChanged">
                </charon-text-input>

                <charon-number-input
                        name="preset_max_result"
                        :required="false"
                        :label="translate('max_points_label')"
                        :input_value="activePreset.max_result"
                        @input-was-changed="onMaxResultChanged">
                </charon-number-input>

                <charon-select
                        :label="translate('grading_method_label')"
                        name="preset_grading_method"
                        :options="gradingMethods"
                        :selected="activePreset.grading_method_code"
                        key_field="code"
                        @input-was-changed="onGradingMethodChanged">
                </charon-select>

                <grades-section
                        :gradeTypes="gradeTypes"
                        :grades="activePreset.grades"
                        :gradeNamePrefixes="gradeNamePrefixes">
                </grades-section>

                <charon-text-input
                        input_name="preset_calculation_formula"
                        :required="false"
                        :input_label="translate('calculation_formula_label')"
                        :input_value="activePreset.calculation_formula"
                        @input-was-changed="onCalculationFormulaChanged">
                </charon-text-input>

                <a class="btn btn-primary"
                   @click="savePreset">
                    {{ translate('save_preset') }}
                </a>

            </div>

        </slot>

    </charon-fieldset>
    
</template>

<script>
    import CharonFieldset from '../form/CharonFieldset.vue';
    import CharonSelect from '../form/CharonSelect.vue';
    import CharonTextInput from '../form/CharonTextInput.vue';
    import CharonNumberInput from '../form/CharonNumberInput.vue';
    import GradesSection from './GradesSection.vue';

    import Translate from '../../mixins/translate';

    export default {

        mixins: [ Translate ],

        components: { CharonFieldset, CharonSelect, CharonTextInput, CharonNumberInput, GradesSection },

        props: {
            presets: { required: true },
            gradingMethods: { required: true },
            gradeTypes: { required: true },
            gradeNamePrefixes: { required: true },
        },

        data() {
            return {
                activePreset: null
            };
        },

        methods: {
            onActivePresetChanged(preset) {
                console.log("Selected!");
                console.log(preset);
            },

            createPreset() {
                this.activePreset = {
                    name: '',
                    parent_category_id: null,
                    calculation_formula: null,
                    extra: '',
                    grading_method_code: null,
                    max_result: null,
                    grades: [ ]
                };
            },

            onNameChanged(name) {
                this.activePreset.name = name;
            },

            onExtraChanged(extra) {
                this.activePreset.extra = extra;
            },

            onMaxResultChanged(maxResult) {
                this.activePreset.max_result = maxResult;
            },

            onGradingMethodChanged(gradingMethodCode) {
                this.activePreset.grading_method_code = gradingMethodCode;
            },

            onCalculationFormulaChanged(calculationFormula) {
                this.activePreset.calculation_formula = calculationFormula;
            },

            savePreset() {
                this.presets.push(this.activePreset);
            }
        }
    }
</script>
