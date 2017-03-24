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

        <input type="hidden" name="extra" :value="form.fields.extra">
        <input type="hidden" name="tester_type" :value="form.fields.tester_type">

    </div>
</template>

<script>
    import { CharonTextInput } from '../../../components/form';
    import { Translate, EmitEventOnInputChange } from '../../../mixins';

    export default {
        mixins: [ Translate, EmitEventOnInputChange ],

        components: { CharonTextInput },

        props: [ 'form' ],
    }
</script>
