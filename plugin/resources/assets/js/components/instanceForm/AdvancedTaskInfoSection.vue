<template>
    <instance-form-fieldset>
        <template slot="title">{{ translate('task_info_title') }}</template>

        <slot>

            <charon-text-input
                    input_name="name"
                    :input_label="translate('task_name_label')"
                    required="true"
                    :input_value="form.fields.name"
                    @input-was-changed="onNameChanged"
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
                    :options="form.tester_types"
                    :selected="form.fields.tester_type"
                    @input-was-changed="onTesterTypeChanged"
            >
            </charon-select>

        </slot>
    </instance-form-fieldset>
</template>

<script>
    import InstanceFormFieldset from '../form/InstanceFormFieldset.vue';
    import CharonTextInput from '../form/CharonTextInput.vue';
    import CharonSelect from '../form/CharonSelect.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { InstanceFormFieldset, CharonTextInput, CharonSelect },

        props: [ 'form' ],

        methods: { }
    }
</script>
