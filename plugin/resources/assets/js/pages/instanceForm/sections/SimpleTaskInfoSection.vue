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
            {{ translate('tester_extra_label') }}:
            {{ (form.fields.tester_extra && (form.fields.tester_extra.length > 0)) ? form.fields.tester_extra : '(No tester extra specified)' }}
        </p>

        <p>
            {{ translate('system_extra_label') }}:
            {{ (form.fields.system_extra && (form.fields.system_extra.length > 0)) ? form.fields.system_extra : '(No system extra specified)' }}
        </p>

        <input type="hidden" name="tester_extra" :value="form.fields.tester_extra">
        <input type="hidden" name="system_extra" :value="form.fields.system_extra">
        <input type="hidden" name="tester_type_code" :value="form.fields.tester_type_code">

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
