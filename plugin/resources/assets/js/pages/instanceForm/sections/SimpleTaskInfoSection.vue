<template>
    <div>

        <charon-text-input
                name="name"
                :label="translate('task_name_label')"
                :required="true"
                :value="form.fields.name"
                :helper_text="translate('task_name_helper')"
                @input-was-changed="onNameChanged">
        </charon-text-input>

        <charon-text-input
                name="project_folder"
                input_class="is-half"
                :label="translate('project_folder_name_label')"
                :required="true"
                :value="form.fields.project_folder"
                :helper_text="translate('project_folder_name_helper')"
                @input-was-changed="onProjectFolderChanged" >
        </charon-text-input>

        <p>
            Extra: {{ form.fields.extra.length > 0 ? form.fields.extra : '(No extra specified)' }}
        </p>

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
