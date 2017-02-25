<template>
    <div>

        <charon-text-input
                input_name="name"
                :input_label="translate('task_name_label')"
                :required="true"
                :input_value="form.fields.name"
                @input-was-changed="onNameChanged">
        </charon-text-input>

        <charon-text-input
                input_name="project_folder"
                :input_label="translate('project_folder_name_label')"
                :required="true"
                :input_value="form.fields.project_folder"
                @input-was-changed="onProjectFolderChanged" >
        </charon-text-input>

    </div>
</template>

<script>
    import CharonTextInput from '../form/CharonTextInput.vue';

    import Translate from '../../mixins/translate';
    import EmitEventOnInputChange from '../../mixins/emitEventOnInputChange';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonTextInput },

        props: [ 'form' ],
    }
</script>
