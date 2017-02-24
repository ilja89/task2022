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

            </div>

        </slot>

    </charon-fieldset>
    
</template>

<script>
    import CharonFieldset from '../form/CharonFieldset.vue';
    import CharonSelect from '../form/CharonSelect.vue';
    import CharonTextInput from '../form/CharonTextInput.vue';
    import CharonNumberInput from '../form/CharonNumberInput.vue';

    import Translate from '../../mixins/translate';

    export default {

        mixins: [ Translate ],

        components: { CharonFieldset, CharonSelect, CharonTextInput, CharonNumberInput },

        props: {
            presets: { required: true }
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
            }
        }
    }
</script>
