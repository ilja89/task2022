<template>

    <charon-fieldset>
        <template slot="title">{{ translate('presets_title') }}</template>

        <slot>

            <div class="preset-select-container">
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
            </div>

            <div v-if="activePreset !== null">

                <charon-text-input
                        class="is-half"
                        input_name="preset_name"
                        :required="false"
                        :input_label="translate('preset_name_label')"
                        :input_value="activePreset.name"
                        :helper_text="translate('preset_name_helper')"
                        @input-was-changed="onNameChanged">
                </charon-text-input>

                <charon-text-input
                        input_name="preset_extra"
                        :required="false"
                        :input_label="translate('extra_label')"
                        :input_value="activePreset.extra"
                        :helper_text="translate('extra_cs_helper')"
                        @input-was-changed="onExtraChanged">
                </charon-text-input>

                <charon-number-input
                        class="is-quarter"
                        name="preset_max_result"
                        :required="false"
                        :label="translate('max_points_label')"
                        :input_value="activePreset.max_result"
                        :helper_text="translate('max_points_cs_helper')"
                        @input-was-changed="onMaxResultChanged">
                </charon-number-input>

                <charon-select
                        :label="translate('grading_method_label')"
                        name="preset_grading_method"
                        :options="gradingMethods"
                        :selected="activePreset.grading_method_code"
                        key_field="code"
                        :helper_text="translate('grading_method_cs_helper')"
                        @input-was-changed="onGradingMethodChanged">
                </charon-select>

                <grades-section
                        :grades="activePreset.preset_grades"
                        :gradeNamePrefixes="gradeNamePrefixes">
                </grades-section>

                <charon-text-input
                        input_name="preset_calculation_formula"
                        :required="false"
                        :input_label="translate('calculation_formula_label')"
                        :input_value="activePreset.calculation_formula"
                        :helper_text="translate('calculation_formula_cs_helper')"
                        @input-was-changed="onCalculationFormulaChanged">
                </charon-text-input>

                <a v-if="!isEditing"
                   class="btn btn-primary"
                   @click="savePreset">
                    {{ translate('save_preset') }}
                </a>

                <a v-else
                   class="btn btn-primary update-preset-btn"
                   @click="updatePreset">
                    {{ translate('update_preset') }}
                </a>

            </div>

        </slot>

    </charon-fieldset>
    
</template>

<script>
    import { CharonFieldset, CharonSelect, CharonTextInput, CharonNumberInput } from '../../../components/form';
    import GradesSection from './GradesSection.vue';

    import { Translate } from '../../../mixins';
    import { Preset } from '../../../models';

    export default {

        mixins: [ Translate ],

        components: { CharonFieldset, CharonSelect, CharonTextInput, CharonNumberInput, GradesSection },

        props: {
            presets: { required: true },
            gradingMethods: { required: true },
            gradeNamePrefixes: { required: true },
            courseId: { required: true },
        },

        data() {
            return {
                activePreset: null
            };
        },

        computed: {
            isEditing() {
                return this.activePreset !== null && typeof this.activePreset.id !== 'undefined' && this.activePreset.id !== null;
            }
        },

        methods: {
            onActivePresetChanged(presetId) {
                this.presets.forEach(preset => {
                    if (preset.id === presetId) {
                        this.activePreset = preset;
                    }
                });
            },

            createPreset() {
                this.activePreset = {
                    name: '',
                    parent_category_id: null,
                    calculation_formula: null,
                    extra: '',
                    grading_method_code: null,
                    max_result: null,
                    preset_grades: [ ]
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
                Preset.save(this.activePreset, this.courseId, preset => {
                    this.presets.push(preset);
                });
            },

            updatePreset() {
                Preset.update(this.activePreset, this.courseId, preset => {
                    let index = null;
                    this.presets.forEach((presetLoop, indexLoop)=> {
                        if (preset.id === presetLoop.id) {
                            index = indexLoop;
                        }
                    });
                    this.presets[index] = preset;
                });
            }
        }
    }
</script>
