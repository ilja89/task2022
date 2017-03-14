<template>
    <div>
        <charon-text-input
                input_name="name"
                :input_label="translate('task_name_label')"
                :required="true"
                :input_value="form.fields.name"
                @input-was-changed="onNameChanged" >
        </charon-text-input>

        <charon-text-input
                input_name="project_folder"
                :input_label="translate('project_folder_name_label')"
                :required="true"
                :input_value="form.fields.project_folder"
                @input-was-changed="onProjectFolderChanged" >
        </charon-text-input>

        <charon-text-input
                input_name="extra"
                :input_label="translate('extra_label')"
                :required="false"
                :input_value="form.fields.extra"
                @input-was-changed="onExtraChanged" >
        </charon-text-input>

        <charon-select
                :label="translate('tester_type_label')"
                name="tester_type"
                :options="form.tester_types"
                :selected="form.fields.tester_type"
                @input-was-changed="onTesterTypeChanged" >
        </charon-select>

    </div>
</template>

<script>
    import CharonTextInput from '../form/CharonTextInput.vue';
    import CharonSelect from '../form/CharonSelect.vue';

    import { Translate, EmitEventOnInputChange } from '../../mixins';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonTextInput, CharonSelect },

        props: [ 'form' ]
    }
</script>
