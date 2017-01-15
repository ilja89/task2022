<template>

    <div>

        <instance-form-fieldset>
            <template slot="title">{{ translate('task_info_title') }}</template>

            <slot>

                <charon-text-input
                        input_name="name"
                        :input_label="translate('task_name_label')"
                        required="true"
                        :input_value="form.fields.name"
                        @input-was-changed="onNameUpdated"
                >
                </charon-text-input>

                <charon-text-input
                        input_name="project_folder"
                        :input_label="translate('project_folder_name_label')"
                        required="true"
                        :input_value="form.fields.project_folder"
                        @input-was-changed="onProjectFolderChanged"
                >
                </charon-text-input>

                <charon-select
                        :label="translate('tester_type_label')"
                        name="tester_type"
                        :options="tester_types"
                        :selected="form.fields.tester_type"
                        @input-was-changed="onTesterTypeChanged"
                >
                </charon-select>

            </slot>
        </instance-form-fieldset>

        <instance-form-fieldset>
            <template slot="title">{{ translate('grading_title') }}</template>

            <slot>

                <charon-select
                        :label="translate('grading_method_label')"
                        name="grading_method"
                        :options="grading_methods"
                        :selected="form.fields.grading_method"
                        @input-was-changed="onGradingMethodChanged"
                >
                </charon-select>

            </slot>

        </instance-form-fieldset>

    </div>

</template>

<script>
    import InstanceFormFieldset from './../form/InstanceFormFieldset.vue';
    import CharonTextInput from './../form/CharonTextInput.vue';
    import CharonSelect from './../form/CharonSelect.vue';

    export default {
        props: [
            'grade_types', 'grading_methods', 'tester_types', 'form'
        ],

        components: {
            InstanceFormFieldset, CharonTextInput, CharonSelect
        },

        methods: {
            translate(string) {
                return window.translations[string];
            },

            onNameUpdated(name) {
                VueEvent.$emit('name-was-changed', name);
            },

            onProjectFolderChanged(projectFolder) {
                VueEvent.$emit('project-folder-was-changed', projectFolder);
            },

            onTesterTypeChanged(testerType) {
                VueEvent.$emit('tester-type-was-changed', testerType);
            },

            onGradingMethodChanged(gradingMethod) {
                VueEvent.$emit('grading-method-was-changed', gradingMethod);
            }
        }
    }
</script>
