<template>

    <charon-fieldset>
        <template slot="title">{{ translate('presets_title') }}</template>

        <slot>

            <div class="preset-select-container">
                <charon-select
                        :label="translate('edit_preset_label')"
                        name="active_preset"
                        :options="presets"
                        :value="null"
                        key_field="id"
                        @input-was-changed="onActivePresetChanged">
                </charon-select>

                <a @click="createPreset" style="margin-top: 1rem" class="btn btn-default add-preset-btn">
                    Add new preset
                </a>
            </div>

            <div v-if="activePreset !== null">

                <charon-text-input
                        input_class="is-half"
                        name="preset_name"
                        :required="false"
                        :label="translate('preset_name_label')"
                        :value="activePreset.name"
                        :helper_text="translate('preset_name_helper')"
                        @input-was-changed="onNameChanged">
                </charon-text-input>

                <charon-text-input
                    name="preset_tester_extra"
                    :required="false"
                    :label="translate('tester_extra_label')"
                    :value="activePreset.tester_extra"
                    :helper_text="translate('tester_extra_cs_helper')"
                    :autocomplete="false"
                    @input-was-changed="onTesterExtraChanged">
                </charon-text-input>

                <charon-text-input
                        name="preset_system_extra"
                        :required="false"
                        :label="translate('system_extra_label')"
                        :value="activePreset.system_extra"
                        :helper_text="translate('system_extra_cs_helper')"
                        :autocomplete="false"
                        @input-was-changed="onSystemExtraChanged">
                </charon-text-input>

                <charon-number-input
                        input_class="is-quarter"
                        name="preset_max_result"
                        :required="false"
                        :label="translate('max_points_label')"
                        :value="activePreset.max_result"
                        :helper_text="translate('max_points_cs_helper')"
                        @input-was-changed="onMaxResultChanged">
                </charon-number-input>

                <charon-select
                        :label="translate('grading_method_label')"
                        name="preset_grading_method"
                        :options="gradingMethods"
                        :value="activePreset.grading_method_code"
                        key_field="code"
                        :helper_text="translate('grading_method_cs_helper')"
                        @input-was-changed="onGradingMethodChanged">
                </charon-select>

                <grades-section
                        :grades="activePreset.preset_grades"
                        :gradeNamePrefixes="gradeNamePrefixes">
                </grades-section>

                <charon-text-input
                        name="preset_calculation_formula"
                        :required="false"
                        :label="translate('calculation_formula_label')"
                        :value="activePreset.calculation_formula"
                        :helper_text="translate('calculation_formula_cs_helper')"
                        @input-was-changed="onCalculationFormulaChanged">
                </charon-text-input>

                <a v-if="!isEditing"
                   class="btn btn-primary"
                   @click="savePreset">
                    {{ translate('save_preset') }}
                </a>

                <a v-else-if="isEditing"
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
    import { Preset } from '../../../api';

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
                    tester_extra: '',
                    system_extra: '',
                    grading_method_code: 1,
                    max_result: null,
                    preset_grades: [ ]
                };
            },

            onNameChanged(name) {
                this.activePreset.name = name;
            },

            onTesterExtraChanged(extra) {
                this.activePreset.tester_extra = extra;
            },

            onSystemExtraChanged(extra) {
                this.activePreset.system_extra = extra;
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
