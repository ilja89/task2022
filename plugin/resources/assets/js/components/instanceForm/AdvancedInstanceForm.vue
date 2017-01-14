<template>

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

        </slot>
    </instance-form-fieldset>

</template>

<script>
    import InstanceFormFieldset from './../form/InstanceFormFieldset.vue';
    import CharonTextInput from './../form/CharonTextInput.vue';

    export default {
        props: [
            'grade_types', 'grading_methods', 'tester_types', 'form'
        ],

        components: {
            InstanceFormFieldset, CharonTextInput
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
            }
        }
    }
</script>
